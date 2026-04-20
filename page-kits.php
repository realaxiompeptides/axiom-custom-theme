<?php
/*
Template Name: Axiom Kits Page
*/
defined('ABSPATH') || exit;

get_header();

$kits_products = wc_get_products(array(
    'status'   => 'publish',
    'limit'    => -1,
    'category' => array('kits'),
    'orderby'  => 'menu_order',
    'order'    => 'ASC',
));

function axiom_get_kit_savings_data($kit_product) {
    if (!$kit_product || !is_a($kit_product, 'WC_Product')) {
        return array(
            'has_savings'        => false,
            'kit_qty'            => 0,
            'single_price'       => 0,
            'normal_total'       => 0,
            'kit_total'          => 0,
            'savings_amount'     => 0,
            'savings_percentage' => 0,
        );
    }

    $kit_name  = $kit_product->get_name();
    $kit_price = (float) $kit_product->get_price();

    if ($kit_price <= 0) {
        return array(
            'has_savings'        => false,
            'kit_qty'            => 0,
            'single_price'       => 0,
            'normal_total'       => 0,
            'kit_total'          => 0,
            'savings_amount'     => 0,
            'savings_percentage' => 0,
        );
    }

    preg_match('/(\d+)\s*(vial|vials|kit)?/i', $kit_name, $qty_match);
    $kit_qty = !empty($qty_match[1]) ? (int) $qty_match[1] : 0;

    if ($kit_qty < 2) {
        return array(
            'has_savings'        => false,
            'kit_qty'            => 0,
            'single_price'       => 0,
            'normal_total'       => 0,
            'kit_total'          => 0,
            'savings_amount'     => 0,
            'savings_percentage' => 0,
        );
    }

    $base_name = preg_replace('/\b\d+\s*(vial|vials|kit)?\b/i', '', $kit_name);
    $base_name = preg_replace('/\bkit\b/i', '', $base_name);
    $base_name = trim(preg_replace('/\s+/', ' ', $base_name));

    if ($base_name === '') {
        return array(
            'has_savings'        => false,
            'kit_qty'            => 0,
            'single_price'       => 0,
            'normal_total'       => 0,
            'kit_total'          => 0,
            'savings_amount'     => 0,
            'savings_percentage' => 0,
        );
    }

    $single_candidates = wc_get_products(array(
        'status'  => 'publish',
        'limit'   => -1,
        'search'  => $base_name,
        'exclude' => array($kit_product->get_id()),
    ));

    $single_match = null;

    if (!empty($single_candidates)) {
        foreach ($single_candidates as $candidate) {
            if (!$candidate || !is_a($candidate, 'WC_Product')) {
                continue;
            }

            if (has_term('kits', 'product_cat', $candidate->get_id())) {
                continue;
            }

            $candidate_name = strtolower($candidate->get_name());
            $base_name_lc   = strtolower($base_name);

            if (strpos($candidate_name, $base_name_lc) !== false || strpos($base_name_lc, $candidate_name) !== false) {
                $single_match = $candidate;
                break;
            }
        }
    }

    if (!$single_match) {
        return array(
            'has_savings'        => false,
            'kit_qty'            => $kit_qty,
            'single_price'       => 0,
            'normal_total'       => 0,
            'kit_total'          => $kit_price,
            'savings_amount'     => 0,
            'savings_percentage' => 0,
        );
    }

    $single_price = (float) $single_match->get_price();

    if ($single_price <= 0) {
        return array(
            'has_savings'        => false,
            'kit_qty'            => $kit_qty,
            'single_price'       => 0,
            'normal_total'       => 0,
            'kit_total'          => $kit_price,
            'savings_amount'     => 0,
            'savings_percentage' => 0,
        );
    }

    $normal_total       = $single_price * $kit_qty;
    $savings_amount     = max(0, $normal_total - $kit_price);
    $savings_percentage = $normal_total > 0 ? round(($savings_amount / $normal_total) * 100) : 0;

    return array(
        'has_savings'        => $savings_amount > 0,
        'kit_qty'            => $kit_qty,
        'single_price'       => $single_price,
        'normal_total'       => $normal_total,
        'kit_total'          => $kit_price,
        'savings_amount'     => $savings_amount,
        'savings_percentage' => $savings_percentage,
    );
}

$kits_data = array(
    'title'              => 'Research Kits',
    'subtitle'           => 'Bulk and kit orders fulfilled separately through our international warehouse.',
    'shipping_cost'      => '$70 flat shipping',
    'shipping_window'    => '7–10 business days',
    'payment_method'     => 'Cryptocurrency only',
    'warehouse_label'    => 'International warehouse',
    'contact_url'        => home_url('/contact-us/'),
    'shop_url'           => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/'),
);

$kits_folder = get_template_directory() . '/kits/';
?>

<main class="axiom-kits-page">
  <?php
  foreach (array('hero.php', 'info-strip.php', 'explainer.php', 'grid.php', 'faq.php') as $kits_partial) {
      $kits_partial_path = $kits_folder . $kits_partial;
      if (file_exists($kits_partial_path)) {
          include $kits_partial_path;
      }
  }
  ?>
</main>

<?php get_footer(); ?>
