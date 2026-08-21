<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Free gift threshold.
 */
function axiom_free_shipping_goal_threshold() {
    return 175;
}

/**
 * Real flash free-gift promotion.
 *
 * The countdown is GLOBAL, not per-visitor, and it never resets on refresh.
 * The first request after a new campaign ID is deployed starts a random
 * countdown between 40 and 60 minutes.
 *
 * To launch a NEW flash campaign later, change the campaign ID below.
 * Example: axiom-free-gift-flash-2
 */
function axiom_free_gift_campaign_id() {
    return 'axiom-free-gift-flash-1';
}

function axiom_free_gift_promo_ends_at() {
    static $timestamp = null;

    if ($timestamp !== null) {
        return $timestamp;
    }

    $option_key = 'axiom_free_gift_end_' . sanitize_key(axiom_free_gift_campaign_id());
    $stored_end = (int) get_option($option_key, 0);

    if ($stored_end > 0) {
        $timestamp = $stored_end;
        return $timestamp;
    }

    // Random real flash deadline: 40 to 60 minutes, global for all visitors.
    $seconds = random_int(40 * 60, 60 * 60);
    $timestamp = time() + $seconds;
    add_option($option_key, $timestamp, '', false);

    return $timestamp;
}

function axiom_free_gift_promo_is_active() {
    return time() < axiom_free_gift_promo_ends_at();
}

/**
 * Product configuration for automatic free gifts.
 *
 * GHK-CU is resolved from the product slug and, if variable,
 * the 100mg variation is selected automatically.
 *
 * MT-1 is resolved from the product slug and, if variable,
 * the 10mg variation is selected automatically.
 */
function axiom_free_gift_config() {
    return array(
        array(
            'product_id'       => 63,
            'variation_id'     => 83,
            'display_name'     => 'GHK-CU 100mg',
        ),
        array(
            'slugs'            => array('mt-1'),
            'variation_match'  => '10mg',
            'display_name'     => 'MT-1 10mg',
        ),
    );
}

/**
 * Normalize text for flexible variation matching.
 */
function axiom_free_gift_normalize($value) {
    $value = strtolower(wp_strip_all_tags((string) $value));
    return preg_replace('/[^a-z0-9]+/', '', $value);
}

/**
 * Resolve a configured free gift product/variation.
 */
function axiom_resolve_free_gift_product($gift) {
    /*
     * Exact variable-product mapping.
     * GHK-CU parent = 63, 100mg variation = 83.
     */
    if (!empty($gift['product_id']) && !empty($gift['variation_id'])) {
        $parent_id    = (int) $gift['product_id'];
        $variation_id = (int) $gift['variation_id'];

        $parent    = wc_get_product($parent_id);
        $variation = wc_get_product($variation_id);

        if (!$parent || !$parent->is_type('variable')) {
            error_log('Axiom free gift: expected variable parent product #' . $parent_id . ' was not found.');
            return null;
        }

        if (!$variation || !$variation->is_type('variation')) {
            error_log('Axiom free gift: variation #' . $variation_id . ' was not found or is not a variation.');
            return null;
        }

        if ((int) $variation->get_parent_id() !== $parent_id) {
            error_log(
                'Axiom free gift: variation #' . $variation_id .
                ' belongs to parent #' . (int) $variation->get_parent_id() .
                ', expected parent #' . $parent_id
            );
            return null;
        }

        if (!$variation->is_purchasable()) {
            error_log('Axiom free gift: variation #' . $variation_id . ' is not purchasable.');
            return null;
        }

        if (!$variation->is_in_stock() && !$variation->backorders_allowed()) {
            error_log('Axiom free gift: variation #' . $variation_id . ' is out of stock.');
            return null;
        }

        /*
         * This is the exact attribute format WC_Cart::add_to_cart expects,
         * e.g. attribute_pa_size => 100mg.
         */
        $attributes = $variation->get_variation_attributes();

        error_log(
            'Axiom free gift exact GHK mapping: parent=' . $parent_id .
            ' variation=' . $variation_id .
            ' attributes=' . wp_json_encode($attributes)
        );

        return array(
            'product_id'     => $parent_id,
            'variation_id'   => $variation_id,
            'attributes'     => $attributes,
            'name'           => !empty($gift['display_name']) ? $gift['display_name'] : $variation->get_name(),
            'original_price' => (float) $variation->get_price(),
        );
    }

    $slugs = !empty($gift['slugs']) && is_array($gift['slugs'])
        ? $gift['slugs']
        : array();

    if (!$slugs) {
        return null;
    }

    $page = null;

    foreach ($slugs as $slug) {
        $candidate = get_page_by_path($slug, OBJECT, 'product');

        if ($candidate) {
            $page = $candidate;
            break;
        }
    }

    if (!$page) {
        error_log('Axiom free gift: product slug not found: ' . implode(',', $slugs));
        return null;
    }

    $product = wc_get_product($page->ID);

    if (!$product || !$product->is_purchasable()) {
        error_log('Axiom free gift: product is not purchasable for ID ' . (int) $page->ID);
        return null;
    }

    if (!$product->is_type('variable')) {
        if (!$product->is_in_stock() && !$product->backorders_allowed()) {
            return null;
        }

        return array(
            'product_id'     => (int) $product->get_id(),
            'variation_id'   => 0,
            'attributes'     => array(),
            'name'           => !empty($gift['display_name']) ? $gift['display_name'] : $product->get_name(),
            'original_price' => (float) $product->get_price(),
        );
    }

    /*
     * IMPORTANT: use WooCommerce's own available-variation payload.
     * This gives add_to_cart() the exact variation attributes it expects.
     */
    $available_variations = $product->get_available_variations();

    if (!$available_variations || !is_array($available_variations)) {
        error_log('Axiom free gift: no available variations for ' . $product->get_name());
        return null;
    }

    $preferred_id = !empty($gift['preferred_variation_id'])
        ? (int) $gift['preferred_variation_id']
        : 0;

    $needle = axiom_free_gift_normalize(
        isset($gift['variation_match']) ? $gift['variation_match'] : ''
    );

    $matched = null;

    /* First try the exact preferred variation ID, if supplied. */
    if ($preferred_id) {
        foreach ($available_variations as $variation_data) {
            if (
                !empty($variation_data['variation_id']) &&
                (int) $variation_data['variation_id'] === $preferred_id &&
                !empty($variation_data['is_purchasable']) &&
                !empty($variation_data['is_in_stock'])
            ) {
                $matched = $variation_data;
                break;
            }
        }
    }

    /* If the ID is not actually the live variation ID, match the 100mg/10mg option. */
    if (!$matched && $needle) {
        foreach ($available_variations as $variation_data) {
            if (empty($variation_data['variation_id'])) {
                continue;
            }

            if (isset($variation_data['is_purchasable']) && !$variation_data['is_purchasable']) {
                continue;
            }

            if (isset($variation_data['is_in_stock']) && !$variation_data['is_in_stock']) {
                continue;
            }

            $parts = array();

            if (!empty($variation_data['attributes']) && is_array($variation_data['attributes'])) {
                foreach ($variation_data['attributes'] as $attribute_key => $attribute_value) {
                    $parts[] = $attribute_key;
                    $parts[] = $attribute_value;
                }
            }

            if (!empty($variation_data['variation_description'])) {
                $parts[] = $variation_data['variation_description'];
            }

            $variation_object = wc_get_product((int) $variation_data['variation_id']);

            if ($variation_object) {
                $parts[] = $variation_object->get_name();
                $parts[] = $variation_object->get_sku();
            }

            $haystack = axiom_free_gift_normalize(implode(' ', $parts));

            if (strpos($haystack, $needle) !== false) {
                $matched = $variation_data;
                break;
            }
        }
    }

    if (!$matched || empty($matched['variation_id'])) {
        $ids = array();
        foreach ($available_variations as $variation_data) {
            if (!empty($variation_data['variation_id'])) {
                $ids[] = (int) $variation_data['variation_id'];
            }
        }
        error_log(
            'Axiom free gift: no matching variation for ' . $product->get_name() .
            '. Available IDs: ' . implode(',', $ids)
        );
        return null;
    }

    $variation_id = (int) $matched['variation_id'];
    $variation     = wc_get_product($variation_id);

    if (
        !$variation ||
        !$variation->is_purchasable() ||
        (!$variation->is_in_stock() && !$variation->backorders_allowed())
    ) {
        return null;
    }

    $attributes = !empty($matched['attributes']) && is_array($matched['attributes'])
        ? $matched['attributes']
        : array();

    return array(
        'product_id'     => (int) $product->get_id(),
        'variation_id'   => $variation_id,
        'attributes'     => $attributes,
        'name'           => !empty($gift['display_name']) ? $gift['display_name'] : $variation->get_name(),
        'original_price' => (float) $variation->get_price(),
    );
}

/**
 * Calculate qualifying merchandise subtotal.
 *
 * Free gift lines are excluded so they can never help qualify the order.
 * This intentionally uses current product prices before shipping/tax.
 */
function axiom_free_shipping_goal_subtotal() {
    if (!function_exists('WC') || !WC()->cart) {
        return 0;
    }

    $subtotal = 0;

    foreach (WC()->cart->get_cart() as $cart_item) {
        if (!empty($cart_item['_axiom_free_gift'])) {
            continue;
        }

        $product  = isset($cart_item['data']) ? $cart_item['data'] : null;
        $quantity = isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 0;

        if (!$product || !is_a($product, 'WC_Product') || $quantity < 1) {
            continue;
        }

        $subtotal += (float) $product->get_price() * $quantity;
    }

    return max(0, $subtotal);
}

/**
 * Find an existing auto-added gift line in the cart.
 */
function axiom_find_free_gift_cart_key($product_id, $variation_id = 0) {
    if (!function_exists('WC') || !WC()->cart) {
        return '';
    }

    foreach (WC()->cart->get_cart() as $cart_key => $cart_item) {
        if (empty($cart_item['_axiom_free_gift'])) {
            continue;
        }

        $item_product_id   = isset($cart_item['product_id']) ? (int) $cart_item['product_id'] : 0;
        $item_variation_id = isset($cart_item['variation_id']) ? (int) $cart_item['variation_id'] : 0;

        if ($item_product_id === (int) $product_id && $item_variation_id === (int) $variation_id) {
            return $cart_key;
        }
    }

    return '';
}

/**
 * Add/remove the automatic gifts as the order crosses the $175 threshold.
 */
function axiom_sync_automatic_free_gifts() {
    static $syncing = false;

    if ($syncing || !function_exists('WC') || !WC()->cart) {
        return;
    }

    if (is_admin() && !wp_doing_ajax()) {
        return;
    }

    $syncing = true;

    $qualified = axiom_free_gift_promo_is_active() && axiom_free_shipping_goal_subtotal() >= (float) axiom_free_shipping_goal_threshold();
    $resolved  = array();

    foreach (axiom_free_gift_config() as $gift) {
        $item = axiom_resolve_free_gift_product($gift);

        if ($item) {
            $resolved[] = $item;
        }
    }

    if ($qualified) {
        foreach ($resolved as $gift) {
            $existing_key = axiom_find_free_gift_cart_key($gift['product_id'], $gift['variation_id']);

            if ($existing_key) {
                if ((int) WC()->cart->get_cart_item($existing_key)['quantity'] !== 1) {
                    WC()->cart->set_quantity($existing_key, 1, false);
                }

                if (
                    empty(WC()->cart->cart_contents[$existing_key]['_axiom_free_gift_original_price']) &&
                    isset($gift['original_price'])
                ) {
                    WC()->cart->cart_contents[$existing_key]['_axiom_free_gift_original_price'] = (float) $gift['original_price'];
                }

                continue;
            }

            $added_key = WC()->cart->add_to_cart(
                $gift['product_id'],
                1,
                $gift['variation_id'],
                $gift['attributes'],
                array(
                    '_axiom_free_gift'                => 1,
                    '_axiom_free_gift_name'           => $gift['name'],
                    '_axiom_free_gift_original_price' => isset($gift['original_price']) ? (float) $gift['original_price'] : 0,
                )
            );

            if (!$added_key) {
                error_log(
                    'Axiom free gift: add_to_cart failed for ' . $gift['name'] .
                    ' parent=' . (int) $gift['product_id'] .
                    ' variation=' . (int) $gift['variation_id'] .
                    ' attributes=' . wp_json_encode($gift['attributes'])
                );
            }
        }
    } else {
        foreach (array_keys(WC()->cart->get_cart()) as $cart_key) {
            $cart_item = WC()->cart->get_cart_item($cart_key);

            if (!empty($cart_item['_axiom_free_gift'])) {
                WC()->cart->remove_cart_item($cart_key);
            }
        }
    }

    $syncing = false;
}

/**
 * Keep the automatic gifts synchronized across WooCommerce cart changes.
 */
add_action('woocommerce_cart_loaded_from_session', 'axiom_sync_automatic_free_gifts', 30);
add_action('woocommerce_add_to_cart', 'axiom_sync_automatic_free_gifts', 30);
add_action('woocommerce_cart_item_removed', 'axiom_sync_automatic_free_gifts', 30);
add_action('woocommerce_after_cart_item_quantity_update', 'axiom_sync_automatic_free_gifts', 30);
add_action('woocommerce_cart_emptied', 'axiom_sync_automatic_free_gifts', 30);

/**
 * Force only auto-added gift lines to $0.00.
 */
add_action('woocommerce_before_calculate_totals', 'axiom_make_automatic_free_gifts_free', 20);

function axiom_make_automatic_free_gifts_free($cart) {
    if (!$cart || !is_a($cart, 'WC_Cart')) {
        return;
    }

    foreach ($cart->get_cart() as $cart_item) {
        if (empty($cart_item['_axiom_free_gift'])) {
            continue;
        }

        if (!empty($cart_item['data']) && is_a($cart_item['data'], 'WC_Product')) {
            $cart_item['data']->set_price(0);
        }
    }
}

/**
 * Lock free gift quantity to one and prevent manual removal through standard cart UI.
 */
add_filter('woocommerce_cart_item_quantity', 'axiom_lock_free_gift_quantity_display', 20, 3);

function axiom_lock_free_gift_quantity_display($product_quantity, $cart_item_key, $cart_item) {
    if (empty($cart_item['_axiom_free_gift'])) {
        return $product_quantity;
    }

    return '<span class="axiom-free-gift-qty">1</span>';
}

/**
 * Add a FREE label beneath qualifying gift items.
 */
add_filter('woocommerce_get_item_data', 'axiom_free_gift_item_label', 20, 2);

function axiom_free_gift_item_label($item_data, $cart_item) {
    if (!empty($cart_item['_axiom_free_gift'])) {
        $item_data[] = array(
            'key'   => 'Promotion',
            'value' => 'FREE $175+ Gift',
        );
    }

    return $item_data;
}

/**
 * Render the free gift progress UI.
 */
function axiom_render_free_shipping_goal() {
    if (!function_exists('WC') || !WC()->cart) {
        return;
    }

    $threshold = (float) axiom_free_shipping_goal_threshold();
    $subtotal  = (float) axiom_free_shipping_goal_subtotal();
    $remaining = max(0, $threshold - $subtotal);
    $progress     = $threshold > 0 ? min(100, ($subtotal / $threshold) * 100) : 0;
    $promo_active = axiom_free_gift_promo_is_active();
    $unlocked     = $promo_active && $subtotal >= $threshold;
    $deadline_ms  = axiom_free_gift_promo_ends_at() * 1000;

    $goal_class = !$promo_active ? 'is-ended' : ($unlocked ? 'is-unlocked' : 'is-progress');
    ?>
    <div class="axiom-free-shipping-goal <?php echo esc_attr($goal_class); ?>" data-threshold="<?php echo esc_attr($threshold); ?>" data-subtotal="<?php echo esc_attr($subtotal); ?>" data-promo-end="<?php echo esc_attr($deadline_ms); ?>">
        <div class="axiom-free-shipping-goal__top">
            <span class="axiom-free-shipping-goal__badge">FREE GIFT</span>

            <?php if ($unlocked) : ?>
                <span class="axiom-free-shipping-goal__status">Unlocked</span>
            <?php endif; ?>
        </div>

        <?php if ($promo_active) : ?>
            <div class="axiom-free-shipping-goal__countdown-row">
                <span class="axiom-free-shipping-goal__countdown-label">Flash bonus ends in</span>
                <strong class="axiom-promo-countdown" data-promo-end="<?php echo esc_attr($deadline_ms); ?>">--:--:--</strong>
            </div>
        <?php else : ?>
            <div class="axiom-free-shipping-goal__countdown-row is-ended">
                <span class="axiom-free-shipping-goal__countdown-label">This promotion has ended</span>
            </div>
        <?php endif; ?>

        <div class="axiom-free-shipping-goal__message">
            <?php if (!$promo_active) : ?>
                <span class="axiom-free-shipping-goal__headline">
                    Free gift promotion ended
                </span>
                <span class="axiom-free-shipping-goal__subheadline">
                    Watch the announcement bar for the next Axiom promotion.
                </span>
            <?php elseif ($unlocked) : ?>
                <span class="axiom-free-shipping-goal__headline">
                    GHK-CU 100mg + MT-1 10mg unlocked
                </span>
                <span class="axiom-free-shipping-goal__subheadline">
                    Both gifts were automatically added to your cart for <strong>FREE</strong>.
                </span>
            <?php else : ?>
                <span class="axiom-free-shipping-goal__headline">
                    Get GHK-CU 100mg + MT-1 10mg FREE at $175
                </span>
                <span class="axiom-free-shipping-goal__subheadline">
                    Add <strong><?php echo wp_kses_post(wc_price($remaining)); ?></strong> more to unlock both gifts.
                </span>
            <?php endif; ?>
        </div>

        <div class="axiom-free-shipping-goal__bar-wrap">
            <div class="axiom-free-shipping-goal__bar">
                <span class="axiom-free-shipping-goal__fill" style="width: <?php echo esc_attr($progress); ?>%;"></span>
            </div>

            <div class="axiom-free-shipping-goal__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" focusable="false">
                    <path d="M20 7h-2.18A2.996 2.996 0 0 0 18 6c0-1.66-1.34-3-3-3-1.54 0-2.81 1.16-2.98 2.65h-.04A2.995 2.995 0 0 0 9 3C7.34 3 6 4.34 6 6c0 .35.06.69.18 1H4c-1.1 0-2 .9-2 2v2c0 .74.4 1.38 1 1.72V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-6.28c.6-.34 1-.98 1-1.72V9c0-1.1-.9-2-2-2Zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1h-2V6c0-.55.45-1 1-1Zm-6 0c.55 0 1 .45 1 1v1H9c-.55 0-1-.45-1-1s.45-1 1-1Zm11 6h-7v-2h7v2Zm-9-2v2H4V9h7Zm-7 4h7v6H5v-6Zm9 6v-6h7v6h-7Z"></path>
                </svg>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Return the gift goal markup as HTML for AJAX cart drawer rendering.
 */
function axiom_get_cart_drawer_free_shipping_goal_html() {
    if (!function_exists('WC') || !WC()->cart) {
        return '';
    }

    ob_start();
    axiom_render_free_shipping_goal();
    return trim(ob_get_clean());
}

/**
 * Free gift goal styles.
 */
add_action('wp_enqueue_scripts', 'axiom_enqueue_free_shipping_goal_styles', 30);

function axiom_enqueue_free_shipping_goal_styles() {
    $css = "
    .axiom-free-shipping-goal {
        margin: 0 0 14px;
        padding: 12px 14px 11px;
        border-bottom: 1px solid #e6eef8;
        background: #f8fbff;
    }

    .axiom-free-shipping-goal__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 7px;
    }

    .axiom-free-shipping-goal__badge,
    .axiom-free-shipping-goal__status {
        display: inline-flex;
        align-items: center;
        min-height: 22px;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 10px;
        line-height: 1;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .axiom-free-shipping-goal__badge {
        background: #e5f2ff;
        color: #2279cc;
    }

    .axiom-free-shipping-goal__status {
        background: #e2f7ea;
        color: #16834a;
    }

    .axiom-free-shipping-goal__countdown-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin: 0 0 10px;
        padding: 9px 11px;
        border: 1px solid #ffd6d6;
        border-radius: 12px;
        background: #fff7f7;
    }

    .axiom-free-shipping-goal__countdown-label {
        color: #9f2f2f;
        font-size: 10px;
        line-height: 1.2;
        font-weight: 900;
        letter-spacing: .07em;
        text-transform: uppercase;
    }

    .axiom-promo-countdown {
        min-width: 74px;
        text-align: right;
        color: #d62929;
        font-size: 21px;
        line-height: 1;
        font-weight: 900;
        letter-spacing: .04em;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .axiom-free-shipping-goal__countdown-row.is-critical {
        border-color: #ffbcbc;
        background: #fff0f0;
    }

    .axiom-free-shipping-goal__countdown-row.is-critical .axiom-promo-countdown {
        animation: axiomPromoPulse 1s ease-in-out infinite;
    }

    @keyframes axiomPromoPulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .62; }
    }

    .axiom-free-shipping-goal__countdown-row.is-ended {
        justify-content: center;
        background: #f8fafc;
    }

    .axiom-free-shipping-goal__message {
        margin-bottom: 10px;
    }

    .axiom-free-shipping-goal__headline {
        display: block;
        color: #12203a;
        font-size: 14px;
        line-height: 1.3;
        font-weight: 800;
    }

    .axiom-free-shipping-goal__subheadline {
        display: block;
        margin-top: 3px;
        color: #718096;
        font-size: 12px;
        line-height: 1.35;
        font-weight: 600;
    }

    .axiom-free-shipping-goal__subheadline strong {
        color: #2f8ff2;
        font-weight: 800;
    }

    .axiom-free-shipping-goal__bar-wrap {
        position: relative;
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .axiom-free-shipping-goal__bar {
        position: relative;
        flex: 1 1 auto;
        height: 9px;
        border-radius: 999px;
        background: #dbeafe;
        overflow: hidden;
    }

    .axiom-free-shipping-goal__fill {
        display: block;
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #69b8ff 0%, #3b99f4 100%);
        transition: width .25s ease;
    }

    .axiom-free-shipping-goal__icon {
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        border-radius: 999px;
        background: #fff;
        border: 2px solid #cfe5ff;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .axiom-free-shipping-goal__icon svg {
        width: 17px;
        height: 17px;
        fill: #3b99f4;
    }

    .axiom-free-shipping-goal.is-unlocked {
        background: #f4fbf7;
        border-bottom-color: #dcefe4;
    }

    .axiom-free-shipping-goal.is-unlocked .axiom-free-shipping-goal__headline {
        color: #177c49;
    }

    .axiom-free-shipping-goal.is-unlocked .axiom-free-shipping-goal__fill {
        background: #35a86b;
    }

    .axiom-free-shipping-goal.is-unlocked .axiom-free-shipping-goal__icon {
        border-color: #bfe8d1;
    }

    .axiom-free-shipping-goal.is-unlocked .axiom-free-shipping-goal__icon svg {
        fill: #35a86b;
    }

    .axiom-free-shipping-goal.is-ended {
        background: #f8fafc;
        border-bottom-color: #e5e7eb;
    }

    .axiom-free-shipping-goal.is-ended .axiom-free-shipping-goal__badge {
        background: #eef2f7;
        color: #667085;
    }

    .axiom-free-shipping-goal.is-ended .axiom-free-shipping-goal__bar {
        background: #e5e7eb;
    }

    .axiom-free-shipping-goal.is-ended .axiom-free-shipping-goal__fill {
        width: 0 !important;
        background: #cbd5e1;
    }

    .axiom-free-gift-qty {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        min-height: 30px;
        border-radius: 999px;
        background: #eef7ff;
        color: #247dcd;
        font-weight: 800;
    }

    @media (max-width: 767px) {
        .axiom-free-shipping-goal {
            padding: 11px 12px 10px;
        }

        .axiom-free-shipping-goal__headline {
            font-size: 13px;
        }

        .axiom-free-shipping-goal__subheadline {
            font-size: 11.5px;
        }

        .axiom-free-shipping-goal__countdown-row {
            padding: 7px 9px;
        }

        .axiom-free-shipping-goal__countdown-label {
            font-size: 9.5px;
        }

        .axiom-promo-countdown {
            min-width: 68px;
            font-size: 19px;
        }

        .axiom-free-shipping-goal__icon {
            flex-basis: 32px;
            width: 32px;
            height: 32px;
        }
    }
    ";

    wp_register_style('axiom-free-shipping-goal', false, array(), '1.2.0');
    wp_enqueue_style('axiom-free-shipping-goal');
    wp_add_inline_style('axiom-free-shipping-goal', $css);
}


/**
 * Lightweight countdown timer.
 * Works for both the AJAX cart drawer and the full cart page because it scans
 * the current DOM once per second instead of attaching duplicate timers.
 */
add_action('wp_footer', 'axiom_render_free_gift_countdown_script', 99);

function axiom_render_free_gift_countdown_script() {
    ?>
    <script>
    (function () {
        if (window.__axiomFreeGiftCountdownStarted) {
            return;
        }

        window.__axiomFreeGiftCountdownStarted = true;

        function pad(value) {
            return String(value).padStart(2, '0');
        }

        function updateCountdowns() {
            var nodes = document.querySelectorAll('.axiom-promo-countdown');

            nodes.forEach(function (node) {
                var end = Number(node.getAttribute('data-promo-end') || 0);

                if (!end) {
                    return;
                }

                var remaining = Math.max(0, end - Date.now());

                if (remaining <= 0) {
                    node.textContent = '00:00:00';

                    var goal = node.closest('.axiom-free-shipping-goal');
                    if (goal) {
                        goal.classList.remove('is-progress', 'is-unlocked');
                        goal.classList.add('is-ended');

                        var label = goal.querySelector('.axiom-free-shipping-goal__countdown-label');
                        if (label) {
                            label.textContent = 'Offer ended';
                        }
                    }

                    return;
                }

                var totalSeconds = Math.floor(remaining / 1000);
                var minutes = Math.floor(totalSeconds / 60);
                var seconds = totalSeconds % 60;

                // This campaign is never longer than one hour, so keep the
                // display focused on urgency: MM:SS.
                node.textContent = pad(minutes) + ':' + pad(seconds);

                var row = node.closest('.axiom-free-shipping-goal__countdown-row');
                if (row) {
                    row.classList.toggle('is-critical', remaining <= 10 * 60 * 1000);
                }
            });
        }

        updateCountdowns();
        window.setInterval(updateCountdowns, 1000);
    })();
    </script>
    <?php
}
