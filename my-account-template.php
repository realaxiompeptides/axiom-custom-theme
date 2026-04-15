<?php
/*
Template Name: My Account Template
*/
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main axiom-account-page">
  <div class="container">
    <section class="axiom-account-hero">
      <p class="axiom-account-kicker">My Account</p>
      <h1>Account Center</h1>
      <p class="axiom-account-subtitle">
        Log in, manage your orders, update your details, and review your account activity in one place.
      </p>
    </section>

    <section class="axiom-account-wrapper">
      <?php
      while (have_posts()) :
          the_post();
          the_content();
      endwhile;
      ?>
    </section>
  </div>
</main>

<?php get_footer(); ?>
