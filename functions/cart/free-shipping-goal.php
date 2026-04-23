<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Free shipping goal threshold.
 */
function axiom_free_shipping_goal_threshold() {
    return 250;
}

/**
 * Get subtotal for the free shipping goal.
 * Uses cart contents total, excluding shipping.
 */
function axiom_free_shipping_goal_subtotal() {
    if (!function_exists('WC') || !WC()->cart) {
        return 0;
    }

    return (float) WC()->cart->get_cart_contents_total();
}

/**
 * Render the free shipping goal UI.
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
        <div class="axiom-free-shipping-goal__message">
            <?php if ($unlocked) : ?>
                <span class="axiom-free-shipping-goal__headline">
                    Congrats! You’ve unlocked free shipping
                </span>
            <?php else : ?>
                <span class="axiom-free-shipping-goal__headline">
                    Add items worth <strong><?php echo wp_kses_post(wc_price($remaining)); ?></strong> to unlock free shipping
                </span>
            <?php endif; ?>
        </div>

        <div class="axiom-free-shipping-goal__bar-wrap">
            <div class="axiom-free-shipping-goal__bar">
                <span class="axiom-free-shipping-goal__fill" style="width: <?php echo esc_attr($progress); ?>%;"></span>
            </div>

            <div class="axiom-free-shipping-goal__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" focusable="false">
                    <path d="M3 7.5A1.5 1.5 0 0 1 4.5 6h9A1.5 1.5 0 0 1 15 7.5V9h2.086a1.5 1.5 0 0 1 1.2.6l2.414 3.219c.195.26.3.577.3.902V16.5A1.5 1.5 0 0 1 19.5 18H18a3 3 0 0 1-6 0H9a3 3 0 0 1-6 0H2.5A1.5 1.5 0 0 1 1 16.5v-1A1.5 1.5 0 0 1 2.5 14H3V7.5Zm12 3V14h4.5l-1.928-2.571a.5.5 0 0 0-.4-.2H15Zm-9.5 8a1.5 1.5 0 1 0 0-3a1.5 1.5 0 0 0 0 3Zm9 0a1.5 1.5 0 1 0 0-3a1.5 1.5 0 0 0 0 3Z"></path>
                </svg>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Return the free shipping goal markup as HTML for AJAX cart drawer rendering.
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
 * Free shipping goal styles.
 */
add_action('wp_enqueue_scripts', 'axiom_enqueue_free_shipping_goal_styles', 30);

function axiom_enqueue_free_shipping_goal_styles() {
    $css = "
    .axiom-free-shipping-goal {
        margin: 0 0 18px;
        padding: 4px 0 2px;
    }

    .axiom-free-shipping-goal__message {
        text-align: center;
        margin-bottom: 14px;
    }

    .axiom-free-shipping-goal__headline {
        display: inline-block;
        font-size: 16px;
        line-height: 1.45;
        font-weight: 600;
        color: #6b7280;
        letter-spacing: 0;
    }

    .axiom-free-shipping-goal__headline strong {
        color: #53a7f7;
        font-weight: 800;
    }

    .axiom-free-shipping-goal.is-unlocked .axiom-free-shipping-goal__headline {
        color: #53a7f7;
        font-weight: 800;
    }

    .axiom-free-shipping-goal__bar-wrap {
        position: relative;
        padding-right: 68px;
    }

    .axiom-free-shipping-goal__bar {
        position: relative;
        height: 14px;
        border-radius: 999px;
        background: #dbeafe;
        overflow: hidden;
    }

    .axiom-free-shipping-goal__fill {
        display: block;
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #67b5fb 0%, #53a7f7 100%);
        box-shadow: 0 4px 14px rgba(83, 167, 247, 0.28);
        transition: width 0.3s ease;
    }

    .axiom-free-shipping-goal__icon {
        position: absolute;
        top: 50%;
        right: 0;
        width: 56px;
        height: 56px;
        transform: translateY(-50%);
        border-radius: 999px;
        background: #ffffff;
        border: 3px solid #dbeafe;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .axiom-free-shipping-goal__icon svg {
        width: 25px;
        height: 25px;
        fill: #53a7f7;
    }

    .axiom-free-shipping-goal.is-unlocked .axiom-free-shipping-goal__icon {
        border-color: #53a7f7;
        background: #eef7ff;
    }

    @media (max-width: 767px) {
        .axiom-free-shipping-goal {
            margin: 0 0 16px;
        }

        .axiom-free-shipping-goal__headline {
            font-size: 14px;
            line-height: 1.5;
        }

        .axiom-free-shipping-goal__bar-wrap {
            padding-right: 58px;
        }

        .axiom-free-shipping-goal__bar {
            height: 12px;
        }

        .axiom-free-shipping-goal__icon {
            width: 48px;
            height: 48px;
            border-width: 2px;
        }

        .axiom-free-shipping-goal__icon svg {
            width: 22px;
            height: 22px;
        }
    }
    ";

    wp_register_style('axiom-free-shipping-goal', false, array(), '1.0.0');
    wp_enqueue_style('axiom-free-shipping-goal');
    wp_add_inline_style('axiom-free-shipping-goal', $css);
}
