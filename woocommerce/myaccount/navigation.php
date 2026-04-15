<?php
defined('ABSPATH') || exit;

$customer_id = get_current_user_id();
$first_name  = get_user_meta($customer_id, 'first_name', true);
$last_name   = get_user_meta($customer_id, 'last_name', true);
$display_name = trim($first_name . ' ' . $last_name);

if (!$display_name) {
    $user = wp_get_current_user();
    $display_name = $user ? $user->display_name : '';
}

$initial = $display_name ? strtoupper(mb_substr($display_name, 0, 1)) : 'A';
?>

<nav class="axiom-account-nav">
  <div class="axiom-account-nav-card">
    <div class="axiom-account-user">
      <div class="axiom-account-avatar"><?php echo esc_html($initial); ?></div>
      <div class="axiom-account-user-meta">
        <p class="axiom-account-user-kicker">My Account</p>
        <strong><?php echo esc_html($display_name ?: 'Account'); ?></strong>
      </div>
    </div>

    <ul>
      <?php foreach (wc_get_account_menu_items() as $endpoint => $label) : ?>
        <li class="<?php echo wc_get_account_menu_item_classes($endpoint); ?>">
          <a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>">
            <?php echo esc_html($label); ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</nav>
