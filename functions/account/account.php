<?php
if (!defined('ABSPATH')) {
    exit;
}

function axiom_account_asset_version($relative_path) {
    $full_path = get_template_directory() . $relative_path;

    return file_exists($full_path) ? filemtime($full_path) : time();
}

function axiom_account_assets() {
    if (
        (function_exists('is_account_page') && is_account_page()) ||
        (function_exists('is_page_template') && is_page_template('my-account-template.php')) ||
        is_page('my-account')
    ) {
        $theme_uri = get_template_directory_uri();

        wp_enqueue_style(
            'axiom-account-base',
            $theme_uri . '/assets/css/account/base.css',
            array('axiom-base'),
            axiom_account_asset_version('/assets/css/account/base.css')
        );

        wp_enqueue_style(
            'axiom-account-login',
            $theme_uri . '/assets/css/account/login.css',
            array('axiom-account-base'),
            axiom_account_asset_version('/assets/css/account/login.css')
        );

        wp_enqueue_style(
            'axiom-account-dashboard',
            $theme_uri . '/assets/css/account/dashboard.css',
            array('axiom-account-base'),
            axiom_account_asset_version('/assets/css/account/dashboard.css')
        );

        wp_enqueue_style(
            'axiom-account-navigation',
            $theme_uri . '/assets/css/account/navigation.css',
            array('axiom-account-dashboard'),
            axiom_account_asset_version('/assets/css/account/navigation.css')
        );

        wp_enqueue_style(
            'axiom-account-forms',
            $theme_uri . '/assets/css/account/forms.css',
            array('axiom-account-navigation'),
            axiom_account_asset_version('/assets/css/account/forms.css')
        );

        wp_enqueue_style(
            'axiom-account-details',
            $theme_uri . '/assets/css/account/account-details.css',
            array('axiom-account-forms'),
            axiom_account_asset_version('/assets/css/account/account-details.css')
        );

        wp_enqueue_style(
            'axiom-account-downloads',
            $theme_uri . '/assets/css/account/downloads.css',
            array('axiom-account-details'),
            axiom_account_asset_version('/assets/css/account/downloads.css')
        );

        wp_enqueue_style(
            'axiom-account-gift-cards',
            $theme_uri . '/assets/css/account/gift-cards.css',
            array('axiom-account-downloads'),
            axiom_account_asset_version('/assets/css/account/gift-cards.css')
        );

        wp_enqueue_style(
            'axiom-account-addresses',
            $theme_uri . '/assets/css/account/addresses.css',
            array('axiom-account-gift-cards'),
            axiom_account_asset_version('/assets/css/account/addresses.css')
        );

        wp_enqueue_style(
            'axiom-account-mobile',
            $theme_uri . '/assets/css/account/mobile.css',
            array(
                'axiom-account-base',
                'axiom-account-login',
                'axiom-account-dashboard',
                'axiom-account-navigation',
                'axiom-account-forms',
                'axiom-account-details',
                'axiom-account-downloads',
                'axiom-account-gift-cards',
                'axiom-account-addresses',
            ),
            axiom_account_asset_version('/assets/css/account/mobile.css')
        );

        wp_enqueue_style(
            'axiom-account',
            $theme_uri . '/assets/css/account/account.css',
            array(
                'axiom-account-base',
                'axiom-account-login',
                'axiom-account-dashboard',
                'axiom-account-navigation',
                'axiom-account-forms',
                'axiom-account-details',
                'axiom-account-downloads',
                'axiom-account-gift-cards',
                'axiom-account-addresses',
                'axiom-account-mobile',
            ),
            axiom_account_asset_version('/assets/css/account/account.css')
        );

        wp_enqueue_style(
            'axiom-account-orders-final',
            $theme_uri . '/assets/css/account/orders.css',
            array('axiom-account'),
            axiom_account_asset_version('/assets/css/account/orders.css')
        );

        $points_rewards_css = '/assets/css/account/points-rewards.css';

        if (file_exists(get_template_directory() . $points_rewards_css)) {
            wp_enqueue_style(
                'axiom-account-points-rewards-final',
                $theme_uri . $points_rewards_css,
                array('axiom-account-orders-final'),
                axiom_account_asset_version($points_rewards_css)
            );
        }

        $account_js = '/assets/js/account/account.js';

        if (file_exists(get_template_directory() . $account_js)) {
            wp_enqueue_script(
                'axiom-account-js',
                $theme_uri . $account_js,
                array(),
                axiom_account_asset_version($account_js),
                true
            );
        }
    }
}
add_action('wp_enqueue_scripts', 'axiom_account_assets', 999);

/**
 * Force custom view-order template.
 */
remove_action('woocommerce_account_view-order_endpoint', 'woocommerce_account_view_order');

add_action('woocommerce_account_view-order_endpoint', 'axiom_force_custom_view_order_template', 1);

function axiom_force_custom_view_order_template($order_id) {
    $template = get_template_directory() . '/woocommerce/myaccount/view-order.php';

    if (file_exists($template)) {
        include $template;
        return;
    }

    wc_get_template('myaccount/view-order.php', array(
        'order_id' => $order_id,
    ));
}

/**
 * Custom Axiom Rewards balance card above plugin rewards page.
 */
add_action('woocommerce_account_loyalty_reward_endpoint', 'axiom_custom_rewards_balance_card', 1);

function axiom_custom_rewards_balance_card() {
    if (!is_user_logged_in()) {
        return;
    }

    $user_id = get_current_user_id();
    $points = get_user_meta($user_id, 'wlr_points', true);

    if ($points === '' || $points === false) {
        $points = 0;
    }

    $points = (int) $points;

    $reward_points_needed = 100;
    $reward_value = 5;

    $current_cycle_points = $points % $reward_points_needed;

    if ($points > 0 && $current_cycle_points === 0) {
        $progress = 100;
        $next_text = 'Ready to redeem';
    } else {
        $progress = min(100, ($current_cycle_points / $reward_points_needed) * 100);
        $points_to_go = $reward_points_needed - $current_cycle_points;
        $next_text = $points_to_go . ' to next reward';
    }

    $earned_rewards = floor($points / $reward_points_needed);
    $earned_value = $earned_rewards * $reward_value;

    ?>
    <section class="axiom-rewards-custom-top">
        <div class="axiom-rewards-balance-card">
            <p class="axiom-rewards-kicker">Axiom Rewards</p>

            <div class="axiom-rewards-card-main">
                <div>
                    <h2>Points Balance</h2>
                    <div class="axiom-rewards-points-number">
                        <?php echo esc_html($points); ?>
                    </div>
                </div>

                <div class="axiom-rewards-earned-pill">
                    $<?php echo esc_html($earned_value); ?> earned
                </div>
            </div>

            <div class="axiom-rewards-progress-info">
                <span>$<?php echo esc_html($reward_value); ?> off every <?php echo esc_html($reward_points_needed); ?> points</span>
                <strong><?php echo esc_html($next_text); ?></strong>
            </div>

            <div class="axiom-rewards-progress-bar">
                <span style="width: <?php echo esc_attr($progress); ?>%;"></span>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Custom Axiom Downloads page.
 */
remove_action('woocommerce_account_downloads_endpoint', 'woocommerce_account_downloads');

add_action('woocommerce_account_downloads_endpoint', 'axiom_custom_downloads_page', 1);

function axiom_custom_downloads_page() {
    if (!is_user_logged_in()) {
        return;
    }

    $downloads = wc_get_customer_available_downloads(get_current_user_id());

    ?>
    <section class="axiom-custom-downloads-page">
        <div class="axiom-downloads-card">
            <div class="axiom-downloads-icon">↓</div>

            <p class="axiom-downloads-kicker">Downloads</p>

            <?php if (empty($downloads)) : ?>
                <h2>No downloads yet</h2>
                <p class="axiom-downloads-text">
                    Downloadable files from your orders will appear here once they are available.
                </p>

                <a class="axiom-downloads-button" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">
                    Browse products
                </a>
            <?php else : ?>
                <h2>Your downloads</h2>

                <div class="axiom-downloads-list">
                    <?php foreach ($downloads as $download) : ?>
                        <a class="axiom-download-item" href="<?php echo esc_url($download['download_url']); ?>">
                            <span><?php echo esc_html($download['download_name']); ?></span>
                            <strong>Download</strong>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php
}

/**
 * Custom Axiom Gift Cards page.
 */
remove_action('woocommerce_account_giftcards_endpoint', 'woocommerce_account_giftcards');
remove_action('woocommerce_account_gift-cards_endpoint', 'woocommerce_account_giftcards');
remove_action('woocommerce_account_gift_cards_endpoint', 'woocommerce_account_giftcards');

add_action('woocommerce_account_giftcards_endpoint', 'axiom_custom_giftcards_page', 999);
add_action('woocommerce_account_gift-cards_endpoint', 'axiom_custom_giftcards_page', 999);
add_action('woocommerce_account_gift_cards_endpoint', 'axiom_custom_giftcards_page', 999);

function axiom_custom_giftcards_page() {
    if (!is_user_logged_in()) {
        return;
    }

    ?>
    <section class="axiom-custom-giftcards-page">
        <div class="axiom-giftcards-hero">
            <div class="axiom-giftcards-icon">
                <i class="fa-solid fa-gift" aria-hidden="true"></i>
            </div>

            <p class="axiom-giftcards-kicker">Gift Cards</p>
            <h2>Your Balance</h2>

            <div class="axiom-giftcards-balance">$0.00</div>
        </div>

        <div class="axiom-giftcards-add-card">
            <h3>Add a gift card</h3>
            <p>Enter your gift card code below to add it to your account.</p>

            <form method="post" class="axiom-giftcards-form">
                <input
                    type="text"
                    name="wc_gc_code"
                    placeholder="Enter gift card code..."
                    autocomplete="off"
                >

                <button type="submit" name="wc_gc_add_gift_card_to_account">
                    Add to account
                </button>
            </form>
        </div>

        <div class="axiom-giftcards-section">
            <h3>Active Gift Cards</h3>
            <div class="axiom-giftcards-empty">
                No active gift cards yet.
            </div>
        </div>

        <div class="axiom-giftcards-section">
            <h3>Activity</h3>
            <div class="axiom-giftcards-empty">
                No activity recorded yet.
            </div>
        </div>
    </section>
    <?php
}
