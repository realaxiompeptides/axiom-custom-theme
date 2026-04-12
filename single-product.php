<?php
defined('ABSPATH') || exit;

get_header();

if (have_posts()) :
  while (have_posts()) :
    the_post();
    include get_template_directory() . '/product-page/product-page.php';
  endwhile;
endif;

get_footer();
