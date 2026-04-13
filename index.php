<?php
defined('ABSPATH') || exit;

get_header();
?>

<main class="site-page-main">
  <div class="container">
    <?php if (have_posts()) : ?>
      <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('site-page-entry'); ?>>
          <?php the_content(); ?>
        </article>
      <?php endwhile; ?>
    <?php else : ?>
      <div class="site-page-entry">
        <h1>Nothing Found</h1>
        <p>No content was found for this page.</p>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php
get_footer();
