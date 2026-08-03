<?php
defined('ABSPATH') || exit;

/**
 * Axiom International Bank Transfer payment gateway.
 *
 * Displays only for customers whose billing/shipping country
 * is outside the United States.
 */

function axiom_register_international_bank_transfer_class() {
    if (
        !class_exists('WC_Payment_Gateway') ||
        class_exists('Axiom_WC_Gateway_International_Bank_Transfer')
    ) {
        return;
    }

    class Axiom_WC_Gateway_International_Bank_Transfer extends WC_Payment_Gateway {

        public function __construct() {
            $this->id                 = 'axiom_international_bank_transfer';
            $this->icon               = '';
            $this->has_fields         = true;
            $this->method_title       = __('International Bank Transfer', 'axiom');
            $this->method_description = __(
                'Accept international wire transfers and display copy-ready payment instructions after checkout.',
                'axiom'
            );

            $this->supports = array('products');

            $this->init_form_fields();
            $this->init_settings();

            $this->enabled      = $this->get_option('enabled', 'no');
            $this->title        = $this->get_option(
                'title',
                __('International Bank Transfer', 'axiom')
            );
            $this->description  = $this->get_option(
                'description',
                __('Pay securely by international wire transfer. Your copy-ready bank details will appear after you place your order.', 'axiom')
            );
            $this->order_status = $this->get_option('order_status', 'on-hold');

            add_action(
                'woocommerce_update_options_payment_gateways_' . $this->id,
                array($this, 'process_admin_options')
            );
        }

        /**
         * Gateway settings shown in WooCommerce admin.
         */
        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => __('Enable/Disable', 'axiom'),
                    'type'    => 'checkbox',
                    'label'   => __('Enable International Bank Transfer', 'axiom'),
                    'default' => 'no',
                ),

                'order_status' => array(
                    'title'       => __('Order Status', 'axiom'),
                    'type'        => 'select',
                    'description' => __(
                        'The order status used while the international transfer is awaiting verification.',
                        'axiom'
                    ),
                    'default'     => 'on-hold',
                    'options'     => array(
                        'on-hold' => __('On hold', 'axiom'),
                        'pending' => __('Pending payment', 'axiom'),
                    ),
                ),

                'title' => array(
                    'title'       => __('Title', 'axiom'),
                    'type'        => 'text',
                    'default'     => __('International Bank Transfer', 'axiom'),
                    'description' => __('The payment method title shown during checkout.', 'axiom'),
                    'desc_tip'    => true,
                ),

                'description' => array(
                    'title'       => __('Description', 'axiom'),
                    'type'        => 'textarea',
                    'default'     => __(
                        'Pay securely by international wire transfer. Your copy-ready bank details will appear after you place your order.',
                        'axiom'
                    ),
                    'description' => __('Shown inside the payment method during checkout.', 'axiom'),
                ),
            );
        }

        /**
         * Only make this gateway available to international customers.
         */
        public function is_available() {
            if (!parent::is_available()) {
                return false;
            }

            $country = '';

            if (function_exists('WC') && WC()->customer) {
                $country = WC()->customer->get_billing_country();

                if (!$country) {
                    $country = WC()->customer->get_shipping_country();
                }
            }

            /*
             * Keep the option visible until a country is selected.
             * Hide it once the customer selects the United States.
             */
            return empty($country) || strtoupper($country) !== 'US';
        }

        /**
         * Styled checkout description.
         */
        public function payment_fields() {
            $description = $this->description
                ? $this->description
                : __('Pay securely by international wire transfer.', 'axiom');
            ?>
            <div class="axiom-ibt-checkout-card">
                <div class="axiom-ibt-checkout-icon" aria-hidden="true">
                    <span>🏦</span>
                </div>

                <div class="axiom-ibt-checkout-content">
                    <div class="axiom-ibt-checkout-description">
                        <?php echo wp_kses_post(wpautop($description)); ?>
                    </div>

                    <div class="axiom-ibt-checkout-badges">
                        <span>
                            <span aria-hidden="true">✓</span>
                            Secure bank payment
                        </span>

                        <span>
                            <span aria-hidden="true">🌎</span>
                            International customers
                        </span>
                    </div>

                    <div class="axiom-ibt-checkout-steps">
                        <div class="axiom-ibt-checkout-step">
                            <strong>1</strong>
                            <span>Place your order</span>
                        </div>

                        <div class="axiom-ibt-checkout-step">
                            <strong>2</strong>
                            <span>Copy the bank details</span>
                        </div>

                        <div class="axiom-ibt-checkout-step">
                            <strong>3</strong>
                            <span>Use your order number as the reference</span>
                        </div>
                    </div>

                    <div class="axiom-ibt-checkout-note">
                        <span class="axiom-ibt-note-icon" aria-hidden="true">i</span>

                        <span>
                            International transfers usually take 1–5 business days.
                            Pay any bank or intermediary fees separately so the complete
                            order total reaches us.
                        </span>
                    </div>
                </div>
            </div>
            <?php
        }

        /**
         * Process the order without an immediate online payment.
         */
        public function process_payment($order_id) {
            $order = wc_get_order($order_id);

            if (!$order) {
                wc_add_notice(
                    __('Unable to create your order. Please try again.', 'axiom'),
                    'error'
                );

                return array(
                    'result' => 'failure',
                );
            }

            $allowed_statuses = array('on-hold', 'pending');

            $status = in_array(
                $this->order_status,
                $allowed_statuses,
                true
            )
                ? $this->order_status
                : 'on-hold';

            $order->update_status(
                $status,
                __('Awaiting international bank transfer.', 'axiom')
            );

            $order->set_payment_method($this->id);
            $order->set_payment_method_title($this->title);
            $order->save();

            /*
             * Reduce stock in the same way as WooCommerce's built-in
             * offline bank transfer gateway.
             */
            wc_reduce_stock_levels($order_id);

            if (function_exists('WC') && WC()->cart) {
                WC()->cart->empty_cart();
            }

            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url($order),
            );
        }
    }
}

/*
 * The theme loads after WordPress plugins, so WooCommerce's gateway
 * class should already exist. Register immediately when possible.
 */
axiom_register_international_bank_transfer_class();

/**
 * Add the gateway to WooCommerce.
 */
add_filter(
    'woocommerce_payment_gateways',
    'axiom_add_international_bank_transfer_gateway'
);

function axiom_add_international_bank_transfer_gateway($gateways) {
    $gateways[] = 'Axiom_WC_Gateway_International_Bank_Transfer';

    return $gateways;
}

/**
 * International wire transfer details.
 */
function axiom_ibt_get_bank_details() {
    return array(
        'beneficiary_name'    => 'Axiom Peptides LLC',
        'account_number'      => '621956274808088',
        'beneficiary_address' => '30 North Gould Street, Sheridan, WY 82801 USA',

        'bank_name'           => 'Column N.A.',
        'swift'               => 'CLNOUS66MER',
        'routing'             => '121145433',
        'alternate_routing'   => '121145307',
        'bank_address'        => '1 Letterman Drive, Building A, Suite A4-700, San Francisco, CA 94129 USA',

        'intermediary_swift'  => 'CHASUS33XXX',
    );
}

/**
 * Render one copyable bank-detail row.
 */
function axiom_ibt_render_copy_row($label, $value, $extra_class = '') {
    ?>
    <div class="axiom-ibt-row <?php echo esc_attr($extra_class); ?>">
        <div class="axiom-ibt-row-content">
            <span class="axiom-ibt-label">
                <?php echo esc_html($label); ?>
            </span>

            <strong class="axiom-ibt-value">
                <?php echo esc_html($value); ?>
            </strong>
        </div>

        <button
            type="button"
            class="axiom-ibt-copy"
            data-copy="<?php echo esc_attr($value); ?>"
            aria-label="<?php echo esc_attr('Copy ' . $label); ?>"
        >
            <span class="axiom-ibt-copy-icon" aria-hidden="true">⧉</span>
            <span class="axiom-ibt-copy-label">Copy</span>
        </button>
    </div>
    <?php
}

/**
 * Display complete wire instructions on the WooCommerce thank-you page.
 */
add_action(
    'woocommerce_thankyou_axiom_international_bank_transfer',
    'axiom_ibt_render_thankyou_instructions',
    5
);

function axiom_ibt_render_thankyou_instructions($order_id) {
    $order = wc_get_order($order_id);

    if (
        !$order ||
        $order->get_payment_method() !== 'axiom_international_bank_transfer'
    ) {
        return;
    }

    $details      = axiom_ibt_get_bank_details();
    $order_number = $order->get_order_number();
    $amount_text  = wp_strip_all_tags($order->get_formatted_order_total());

    $copy_all = implode(
        "\n",
        array(
            'INTERNATIONAL BANK TRANSFER',
            '',
            'Payment amount: ' . $amount_text,
            'Payment reference: ' . $order_number,
            '',
            'BENEFICIARY',
            'Beneficiary name: ' . $details['beneficiary_name'],
            'Account number: ' . $details['account_number'],
            'Beneficiary address: ' . $details['beneficiary_address'],
            '',
            'RECEIVING BANK',
            'Bank name: ' . $details['bank_name'],
            'SWIFT / BIC: ' . $details['swift'],
            'ABA routing number: ' . $details['routing'],
            'Alternate ABA routing number: ' . $details['alternate_routing'],
            'Bank address: ' . $details['bank_address'],
            '',
            'INTERMEDIARY BANK — ONLY IF REQUIRED',
            'Intermediary SWIFT / BIC: ' . $details['intermediary_swift'],
        )
    );
    ?>
    <section
        class="axiom-ibt-wrap"
        aria-labelledby="axiom-ibt-title"
    >
        <div class="axiom-ibt-hero">
            <div class="axiom-ibt-hero-icon" aria-hidden="true">
                🏦
            </div>

            <div class="axiom-ibt-hero-content">
                <span class="axiom-ibt-eyebrow">
                    Payment required
                </span>

                <h2 id="axiom-ibt-title">
                    Complete your international bank transfer
                </h2>

                <p>
                    Your order has been reserved and is awaiting payment.
                    Copy the information below into your bank’s international
                    wire transfer form.
                </p>
            </div>
        </div>

        <div class="axiom-ibt-important-grid">
            <div class="axiom-ibt-important-card">
                <span class="axiom-ibt-important-label">
                    Exact amount to send
                </span>

                <strong class="axiom-ibt-important-value">
                    <?php echo wp_kses_post($order->get_formatted_order_total()); ?>
                </strong>

                <button
                    type="button"
                    class="axiom-ibt-copy axiom-ibt-copy-wide"
                    data-copy="<?php echo esc_attr($amount_text); ?>"
                >
                    <span class="axiom-ibt-copy-icon" aria-hidden="true">⧉</span>
                    <span class="axiom-ibt-copy-label">Copy amount</span>
                </button>
            </div>

            <div class="axiom-ibt-important-card axiom-ibt-reference">
                <span class="axiom-ibt-important-label">
                    Payment reference / memo
                </span>

                <strong class="axiom-ibt-important-value">
                    Order #<?php echo esc_html($order_number); ?>
                </strong>

                <button
                    type="button"
                    class="axiom-ibt-copy axiom-ibt-copy-wide"
                    data-copy="<?php echo esc_attr($order_number); ?>"
                >
                    <span class="axiom-ibt-copy-icon" aria-hidden="true">⧉</span>
                    <span class="axiom-ibt-copy-label">Copy order number</span>
                </button>
            </div>
        </div>

        <div class="axiom-ibt-alert">
            <span class="axiom-ibt-alert-icon" aria-hidden="true">!</span>

            <div>
                <strong>
                    Use <?php echo esc_html($order_number); ?> as your payment reference.
                </strong>

                <p>
                    Enter only your order number in the reference, memo,
                    message, or notes field. This allows us to match your
                    transfer to your order.
                </p>
            </div>
        </div>

        <div class="axiom-ibt-section">
            <div class="axiom-ibt-section-head">
                <span class="axiom-ibt-step">1</span>

                <div>
                    <h3>Beneficiary information</h3>
                    <p>The business receiving your transfer.</p>
                </div>
            </div>

            <?php
            axiom_ibt_render_copy_row(
                'Beneficiary name',
                $details['beneficiary_name']
            );

            axiom_ibt_render_copy_row(
                'Account number',
                $details['account_number']
            );

            axiom_ibt_render_copy_row(
                'Beneficiary address',
                $details['beneficiary_address']
            );
            ?>
        </div>

        <div class="axiom-ibt-section">
            <div class="axiom-ibt-section-head">
                <span class="axiom-ibt-step">2</span>

                <div>
                    <h3>Receiving bank</h3>
                    <p>
                        Enter these details in your bank’s international
                        wire transfer section.
                    </p>
                </div>
            </div>

            <?php
            axiom_ibt_render_copy_row(
                'Bank name',
                $details['bank_name']
            );

            axiom_ibt_render_copy_row(
                'SWIFT / BIC code',
                $details['swift']
            );

            axiom_ibt_render_copy_row(
                'ABA routing number',
                $details['routing']
            );

            axiom_ibt_render_copy_row(
                'Alternate ABA routing number',
                $details['alternate_routing']
            );

            axiom_ibt_render_copy_row(
                'Bank address',
                $details['bank_address']
            );
            ?>

            <div class="axiom-ibt-helper">
                <span class="axiom-ibt-helper-icon" aria-hidden="true">i</span>

                <p>
                    Use routing number
                    <strong><?php echo esc_html($details['routing']); ?></strong>
                    first. If your bank does not recognize it, use
                    <strong><?php echo esc_html($details['alternate_routing']); ?></strong>.
                </p>
            </div>
        </div>

        <details class="axiom-ibt-section axiom-ibt-details">
            <summary>
                <span class="axiom-ibt-details-summary">
                    <span class="axiom-ibt-details-icon" aria-hidden="true">
                        ⇄
                    </span>

                    <span>
                        <strong>Intermediary bank</strong>
                        <small>Open only if your bank asks for one</small>
                    </span>
                </span>

                <span class="axiom-ibt-chevron" aria-hidden="true">⌄</span>
            </summary>

            <div class="axiom-ibt-details-body">
                <p class="axiom-ibt-details-intro">
                    Most customers will not need this field. Enter it only
                    when your bank asks for an intermediary or correspondent bank.
                </p>

                <?php
                axiom_ibt_render_copy_row(
                    'Intermediary SWIFT / BIC',
                    $details['intermediary_swift']
                );
                ?>
            </div>
        </details>

        <button
            type="button"
            class="axiom-ibt-copy-all"
            data-copy="<?php echo esc_attr($copy_all); ?>"
        >
            <span aria-hidden="true">⧉</span>
            <span class="axiom-ibt-copy-label">
                Copy all international wire details
            </span>
        </button>

        <div class="axiom-ibt-fees-notice">
            <span class="axiom-ibt-fees-icon" aria-hidden="true">$</span>

            <div>
                <strong>Make sure we receive the full order amount</strong>

                <p>
                    Your bank or an intermediary bank may charge a transfer fee.
                    Select the option that makes the sender responsible for all
                    fees whenever your bank provides that choice.
                </p>
            </div>
        </div>

        <div class="axiom-ibt-next">
            <span class="axiom-ibt-next-icon" aria-hidden="true">◷</span>

            <div>
                <h3>What happens next?</h3>

                <p>
                    International wires normally arrive within 1–5 business days.
                    We will email you when your payment is received and your order
                    moves into processing.
                </p>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Include the instructions in customer emails.
 */
add_action(
    'woocommerce_email_after_order_table',
    'axiom_ibt_add_email_instructions',
    20,
    4
);

function axiom_ibt_add_email_instructions(
    $order,
    $sent_to_admin,
    $plain_text,
    $email
) {
    if (
        $sent_to_admin ||
        !($order instanceof WC_Order) ||
        $order->get_payment_method() !== 'axiom_international_bank_transfer'
    ) {
        return;
    }

    $details      = axiom_ibt_get_bank_details();
    $order_number = $order->get_order_number();
    $amount       = wp_strip_all_tags($order->get_formatted_order_total());

    if ($plain_text) {
        echo "\n";
        echo "INTERNATIONAL BANK TRANSFER\n";
        echo "---------------------------\n";
        echo 'Amount: ' . $amount . "\n";
        echo 'Payment reference: ' . $order_number . "\n\n";

        echo 'Beneficiary name: ' . $details['beneficiary_name'] . "\n";
        echo 'Account number: ' . $details['account_number'] . "\n";
        echo 'Beneficiary address: ' . $details['beneficiary_address'] . "\n\n";

        echo 'Bank name: ' . $details['bank_name'] . "\n";
        echo 'SWIFT / BIC: ' . $details['swift'] . "\n";
        echo 'ABA routing number: ' . $details['routing'] . "\n";
        echo 'Alternate ABA routing number: ' . $details['alternate_routing'] . "\n";
        echo 'Bank address: ' . $details['bank_address'] . "\n\n";

        echo 'Intermediary SWIFT / BIC, if required: ';
        echo $details['intermediary_swift'] . "\n\n";

        echo 'Use your order number as the payment reference.';
        echo "\nPay all transfer fees separately.\n";

        return;
    }
    ?>
    <div
        style="
            margin:24px 0;
            padding:24px;
            border:1px solid #d6e5f3;
            border-radius:16px;
            background:#f7fbff;
            color:#0f172a;
        "
    >
        <div
            style="
                display:inline-block;
                margin-bottom:10px;
                padding:5px 9px;
                border-radius:999px;
                background:#e7f2ff;
                color:#286fa8;
                font-size:11px;
                font-weight:700;
                text-transform:uppercase;
                letter-spacing:.08em;
            "
        >
            Payment required
        </div>

        <h2
            style="
                margin:0 0 12px;
                color:#0f172a;
                font-size:21px;
                line-height:1.3;
            "
        >
            International bank transfer
        </h2>

        <p style="margin:0 0 16px;line-height:1.6;">
            Send the exact amount below and use your order number as the
            transfer reference.
        </p>

        <table
            cellspacing="0"
            cellpadding="0"
            style="
                width:100%;
                margin:0 0 16px;
                border-collapse:collapse;
            "
        >
            <tr>
                <td
                    style="
                        padding:10px;
                        border:1px solid #d6e5f3;
                        color:#64748b;
                    "
                >
                    Amount
                </td>

                <td
                    style="
                        padding:10px;
                        border:1px solid #d6e5f3;
                        font-weight:700;
                    "
                >
                    <?php echo wp_kses_post($order->get_formatted_order_total()); ?>
                </td>
            </tr>

            <tr>
                <td
                    style="
                        padding:10px;
                        border:1px solid #d6e5f3;
                        color:#64748b;
                    "
                >
                    Payment reference
                </td>

                <td
                    style="
                        padding:10px;
                        border:1px solid #d6e5f3;
                        font-weight:700;
                    "
                >
                    <?php echo esc_html($order_number); ?>
                </td>
            </tr>

            <tr>
                <td
                    style="
                        padding:10px;
                        border:1px solid #d6e5f3;
                        color:#64748b;
                    "
                >
                    Beneficiary
                </td>

                <td
                    style="
                        padding:10px;
                        border:1px solid #d6e5f3;
                    "
                >
                    <?php echo esc_html($details['beneficiary_name']); ?>
                </td>
            </tr>

            <tr>
                <td
                    style="
                        padding:10px;
                        border:1px solid #d6e5f3;
                        color:#64748b;
                    "
                >
                    Account number
                </td>

                <td
                    style="
                        padding:10px;
                        border:1px solid #d6e5f3;
                    "
                >
                    <?php echo esc_html($details['account_number']); ?>
                </td>
            </tr>

            <tr>
                <td
                    style="
                        padding:10px;
                        border:1px solid #d6e5f3;
                        color:#64748b;
                    "
                >
                    SWIFT / BIC
                </td>

                <td
                    style="
                        padding:10px;
                        border:1px solid #d6e5f3;
                    "
                >
                    <?php echo esc_html($details['swift']); ?>
                </td>
            </tr>

            <tr>
                <td
                    style="
                        padding:10px;
                        border:1px solid #d6e5f3;
                        color:#64748b;
                    "
                >
                    ABA routing
                </td>

                <td
                    style="
                        padding:10px;
                        border:1px solid #d6e5f3;
                    "
                >
                    <?php echo esc_html($details['routing']); ?>
                </td>
            </tr>
        </table>

        <p style="margin:0;line-height:1.6;">
            <strong>Important:</strong> Enter
            <strong><?php echo esc_html($order_number); ?></strong>
            in your bank’s payment reference, memo, message, or notes field.
            Pay any bank fees separately.
        </p>
    </div>
    <?php
}

/**
 * Load the stylesheet and copy-button JavaScript on checkout,
 * order-received and customer order pages.
 */
add_action('wp_enqueue_scripts', 'axiom_ibt_enqueue_assets', 35);

function axiom_ibt_enqueue_assets() {
    if (!function_exists('is_checkout')) {
        return;
    }

    $should_load = is_checkout();

    if (function_exists('is_account_page') && is_account_page()) {
        $should_load = true;
    }

    if (!$should_load) {
        return;
    }

    $theme_uri  = get_template_directory_uri();
    $theme_path = get_template_directory();

    $css_file = '/assets/css/checkout/international-bank-transfer.css';
    $js_file  = '/assets/js/checkout/international-bank-transfer.js';

    if (file_exists($theme_path . $css_file)) {
        wp_enqueue_style(
            'axiom-international-bank-transfer',
            $theme_uri . $css_file,
            array(),
            filemtime($theme_path . $css_file)
        );
    }

    if (file_exists($theme_path . $js_file)) {
        wp_enqueue_script(
            'axiom-international-bank-transfer',
            $theme_uri . $js_file,
            array('jquery'),
            filemtime($theme_path . $js_file),
            true
        );
    }
}
