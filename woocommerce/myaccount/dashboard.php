<?php
defined('ABSPATH') || exit;

$user = wp_get_current_user();
$first_name = get_user_meta($user->ID, 'first_name', true);
$name = $first_name ? $first_name : $user->display_name;

$order_count = 0;
$customer_orders = wc_get_orders(array(
    'customer_id' => $user->ID,
    'limit'       => 1,
    'return'      => 'ids',
));
if (!empty($customer_orders)) {
    $order_count = count(wc_get_orders(array(
        'customer_id' => $user->ID,
        'limit'       => -1,
        'return'      => 'ids',
    )));
}
?>

<div class="axiom-account-dashboard">
  <div class="axiom-account-dashboard-hero">
    <p class="axiom-account-kicker">Dashboard</p>
    <h2>Hello <?php echo esc_html($name); ?></h2>
    <p>
      Manage your orders, addresses, downloads, and account details from one place.
    </p>
  </div>

  <div class="axiom-account-dashboard-grid">
    <div class="axiom-account-stat-card">
      <span>Orders</span>
      <strong><?php echo esc_html($order_count); ?></strong>
    </div>

    <div class="axiom-account-stat-card">
      <span>Addresses</span>
      <strong>Manage</strong>
    </div>

    <div class="axiom-account-stat-card">
      <span>Account Details</span>
      <strong>Update</strong>
    </div>
  </div>

  <div class="axiom-account-dashboard-card">
    <h3>Quick actions</h3>
    <div class="axiom-account-quick-links">
      <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>">View Orders</a>
      <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-address')); ?>">Edit Addresses</a>
      <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-account')); ?>">Account Details</a>
      <a href="<?php echo esc_url(wc_logout_url()); ?>">Log out</a>
    </div>
  </div>

  <?php do_action('woocommerce_account_dashboard'); ?>

</div>
