<?php
defined('ABSPATH') || exit;

$customer_orders = wc_get_orders(
    apply_filters(
        'woocommerce_my_account_my_orders_query',
        array(
            'customer' => get_current_user_id(),
            'paginate' => true,
            'page'     => get_query_var('paged') ? absint(get_query_var('paged')) : 1,
            'limit'    => wc_get_account_orders_columns() ? get_option('posts_per_page') : -1,
        )
    )
);

$has_orders = !empty($customer_orders->orders);
?>

<div class="axiom-account-section">
  <div class="axiom-account-dashboard-hero">
    <p class="axiom-account-kicker">Orders</p>
    <h2>Your orders</h2>
    <p>View your recent orders, check statuses, and open order details.</p>
  </div>

  <div class="axiom-shipping-notice-card">
    <div class="axiom-shipping-notice-top">
      <div class="axiom-shipping-notice-icon">
        <i class="fa-solid fa-truck-fast"></i>
      </div>

      <div class="axiom-shipping-notice-content">
        <span>Shipping Notice</span>
        <h3>Carrier delivery times are estimates only</h3>
        <p>
          USPS, UPS, and FedEx delivery windows are estimates only and are not guaranteed unless the selected service specifically includes a carrier-backed guarantee.
          Once your order is accepted by the carrier, delivery delays, missed scans, routing issues, weather delays, and lost packages are outside of our direct control.
        </p>
      </div>
    </div>

    <div class="axiom-shipping-carriers">
      <div>
        <i class="fa-brands fa-usps"></i>
        <strong>USPS</strong>
      </div>

      <div>
        <i class="fa-brands fa-ups"></i>
        <strong>UPS</strong>
      </div>

      <div>
        <i class="fa-brands fa-fedex"></i>
        <strong>FedEx</strong>
      </div>
    </div>

    <p class="axiom-shipping-help">
      If your package is delayed or missing, please contact support and we will help open a carrier investigation with the shipping carrier.
    </p>
  </div>

  <?php if ($has_orders) : ?>
    <div class="axiom-account-dashboard-card axiom-account-orders-card">
      <div class="axiom-account-orders-list">
        <?php foreach ($customer_orders->orders as $customer_order) :
          $order = wc_get_order($customer_order);

          if (!$order) {
            continue;
          }

          $item_count = $order->get_item_count() - $order->get_item_count_refunded();
          ?>
          <div class="axiom-account-order-row">
            <div class="axiom-account-order-main">
              <div class="axiom-account-order-top">
                <span class="axiom-account-order-number">#<?php echo esc_html($order->get_order_number()); ?></span>
                <span class="axiom-account-order-status status-<?php echo esc_attr($order->get_status()); ?>">
                  <?php echo esc_html(wc_get_order_status_name($order->get_status())); ?>
                </span>
              </div>

              <div class="axiom-account-order-meta">
                <span><strong>Date:</strong> <?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></span>
                <span><strong>Total:</strong> <?php echo wp_kses_post($order->get_formatted_order_total()); ?></span>
                <span><strong>Items:</strong> <?php echo esc_html($item_count); ?></span>
              </div>
            </div>

            <div class="axiom-account-order-actions">
              <?php
              $actions = wc_get_account_orders_actions($order);

              foreach ($actions as $key => $action) :
                ?>
                <a href="<?php echo esc_url($action['url']); ?>" class="axiom-account-order-btn axiom-account-order-btn-<?php echo esc_attr($key); ?>">
                  <?php echo esc_html($action['name']); ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if (1 < $customer_orders->max_num_pages) : ?>
        <div class="axiom-account-pagination">
          <?php $current_page = absint(get_query_var('paged') ? get_query_var('paged') : 1); ?>

          <?php if (1 !== $current_page) : ?>
            <a class="axiom-account-order-btn" href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page - 1)); ?>">
              Previous
            </a>
          <?php endif; ?>

          <?php if (intval($customer_orders->max_num_pages) !== $current_page) : ?>
            <a class="axiom-account-order-btn" href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page + 1)); ?>">
              Next
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php else : ?>
    <div class="axiom-account-dashboard-card">
      <p>You have not placed any orders yet.</p>
      <div class="axiom-account-quick-links" style="margin-top:16px;">
        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Start Shopping</a>
      </div>
    </div>
  <?php endif; ?>
</div>
