<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Free gift goal threshold.
 */
function axiom_free_shipping_goal_threshold() {
    return 175;
}

/**
 * Get subtotal for the goal.
 * Uses cart contents total, excluding shipping.
 */
function axiom_free_shipping_goal_subtotal() {
    if (!function_exists('WC') || !WC()->cart) {
        return 0;
    }

    return (float) WC()->cart->get_cart_contents_total();
}

/**
 * Render the free gift goal UI.
 */
function axiom_render_free_shipping_goal() {
    if (!function_exists('WC') || !WC()->cart) {
        return;
    }

    $threshold = (float) axiom_free_shipping_goal_threshold();
    $subtotal  = (float) axiom_free_shipping_goal_subtotal();
    $remaining = max(0, $threshold - $subtotal);
    $progress  = $threshold > 0 ? min(100, ($subtotal / $threshold) * 100) : 0;
    $unlocked  = $subtotal >= $threshold;

    $goal_class = $unlocked ? 'is-unlocked' : 'is-progress';
    ?>
    <div class="axiom-free-shipping-goal <?php echo esc_attr($goal_class); ?>" data-threshold="<?php echo esc_attr($threshold); ?>" data-subtotal="<?php echo esc_attr($subtotal); ?>">
        <div class="axiom-free-shipping-goal__top">
            <span class="axiom-free-shipping-goal__badge">FREE GIFT PROGRESS</span>

            <?php if ($unlocked) : ?>
                <span class="axiom-free-shipping-goal__status">Unlocked</span>
            <?php endif; ?>
        </div>

        <div class="axiom-free-shipping-goal__message">
            <?php if ($unlocked) : ?>
                <span class="axiom-free-shipping-goal__headline">
                    You’ve unlocked FREE GHK-CU 100mg + MT-1 10mg
                </span>
                <span class="axiom-free-shipping-goal__subheadline">
                    Your free gift bundle is now qualified for this order.
                </span>
            <?php else : ?>
                <span class="axiom-free-shipping-goal__headline">
                    Unlock FREE GHK-CU 100mg + MT-1 10mg
                </span>
                <span class="axiom-free-shipping-goal__subheadline">
                    Add <strong><?php echo wp_kses_post(wc_price($remaining)); ?></strong> more to claim your free gift bundle.
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
 * Return the free gift goal markup as HTML for AJAX cart drawer rendering.
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
        margin: 0 0 18px;
        padding: 14px 14px 12px;
        border: 1px solid #dbeafe;
        border-radius: 18px;
        background: linear-gradient(180deg, #f8fbff 0%, #eef6ff 100%);
    }

    .axiom-free-shipping-goal__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }

    .axiom-free-shipping-goal__badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #e0efff;
        color: #2d6fb5;
        font-size: 11px;
        line-height: 1;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .axiom-free-shipping-goal__status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #dff6e8;
        color: #1f8a4d;
        font-size: 11px;
        line-height: 1;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .axiom-free-shipping-goal__message {
        margin-bottom: 12px;
    }

    .axiom-free-shipping-goal__headline {
        display: block;
        font-size: 16px;
        line-height: 1.3;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .axiom-free-shipping-goal__subheadline {
        display: block;
        font-size: 13px;
        line-height: 1.45;
        font-weight: 600;
        color: #64748b;
    }

    .axiom-free-shipping-goal__subheadline strong {
        color: #2f8ff2;
        font-weight: 800;
    }

    .axiom-free-shipping-goal__bar-wrap {
        position: relative;
        padding-right: 50px;
        min-height: 40px;
    }

    .axiom-free-shipping-goal__bar {
        position: relative;
        height: 12px;
        border-radius: 999px;
        background: #dbeafe;
        overflow: hidden;
    }

    .axiom-free-shipping-goal__fill {
        display: block;
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #6ab8ff 0%, #3b99f4 100%);
        box-shadow: 0 4px 14px rgba(59, 153, 244, 0.28);
        transition: width 0.3s ease;
    }

    .axiom-free-shipping-goal__icon {
        position: absolute;
        top: 50%;
        right: 0;
        width: 40px;
        height: 40px;
        transform: translateY(-50%);
        border-radius: 999px;
        background: #ffffff;
        border: 2px solid #cfe5ff;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .axiom-free-shipping-goal__icon svg {
        width: 19px;
        height: 19px;
        fill: #3b99f4;
    }

    .axiom-free-shipping-goal.is-unlocked {
        border-color: #bfe0ff;
        background: linear-gradient(180deg, #f4faff 0%, #eaf4ff 100%);
    }

    .axiom-free-shipping-goal.is-unlocked .axiom-free-shipping-goal__headline {
        color: #1673d3;
    }

    .axiom-free-shipping-goal.is-unlocked .axiom-free-shipping-goal__bar {
        background: #cfe6ff;
    }

    .axiom-free-shipping-goal.is-unlocked .axiom-free-shipping-goal__icon {
        border-color: #7cbcff;
        background: #ffffff;
    }

    @media (max-width: 767px) {
        .axiom-free-shipping-goal {
            margin: 0 0 16px;
            padding: 12px 12px 10px;
            border-radius: 16px;
        }

        .axiom-free-shipping-goal__top {
            gap: 8px;
            margin-bottom: 9px;
        }

        .axiom-free-shipping-goal__badge,
        .axiom-free-shipping-goal__status {
            font-size: 10px;
            min-height: 26px;
            padding: 6px 9px;
        }

        .axiom-free-shipping-goal__headline {
            font-size: 15px;
            line-height: 1.32;
        }

        .axiom-free-shipping-goal__subheadline {
            font-size: 12.5px;
            line-height: 1.45;
        }

        .axiom-free-shipping-goal__bar-wrap {
            padding-right: 46px;
            min-height: 36px;
        }

        .axiom-free-shipping-goal__bar {
            height: 10px;
        }

        .axiom-free-shipping-goal__icon {
            width: 36px;
            height: 36px;
        }

        .axiom-free-shipping-goal__icon svg {
            width: 17px;
            height: 17px;
        }
    }
    ";

    wp_register_style('axiom-free-shipping-goal', false, array(), '1.1.0');
    wp_enqueue_style('axiom-free-shipping-goal');
    wp_add_inline_style('axiom-free-shipping-goal', $css);
}
