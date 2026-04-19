<?php
/*
Template Name: Axiom Reviews Page
*/
defined('ABSPATH') || exit;
get_header();

/*
 * Edit these values directly for now.
 * Later you can move them into ACF/options/Supabase if you want.
 */
$reviews_page_data = array(
    'brand_name'        => 'Axiom Peptides',
    'brand_url'         => 'axiomresearch.shop',
    'headline'          => 'Verified Research Reviews',
    'subheadline'       => 'Trusted feedback from researchers and repeat customers.',
    'trust_score'       => '4.9',
    'total_reviews'     => '4,800+',
    'founded'           => '2024',
    'company_size'      => 'Small Team',
    'telegram_url'      => 'https://t.me/yourtelegramhere',
    'whatsapp_url'      => 'https://wa.me/15555555555',
    'top_mentions'      => array(
        'fast shipping',
        'great quality',
        'third-party tested',
        'good communication',
        'BPC-157',
        'GLP-3 RT',
        'USA fulfilled orders',
        'research grade'
    ),
    'rating_distribution' => array(
        5 => 82,
        4 => 11,
        3 => 4,
        2 => 2,
        1 => 1,
    ),
);

$reviews_items = array(
    array(
        'name' => 'Mike T.',
        'country' => 'US',
        'review_count' => '6 reviews',
        'date' => 'Jan 28, 2026',
        'title' => 'GLP-3 RT from Axiom is the real deal',
        'body' => 'Fifth order from Axiom now and the quality has been consistently strong. I sent my last batch for third-party testing and the results came back excellent. Shipping was quick and communication was smooth from start to finish.',
        'experience_date' => 'January 25, 2026',
        'badge' => 'Verified purchase',
        'rating' => 5,
        'image' => '',
    ),
    array(
        'name' => 'Jessica W.',
        'country' => 'UK',
        'review_count' => '2 reviews',
        'date' => 'Jan 26, 2026',
        'title' => 'BPC-157 and TB-500 combo arrived perfectly',
        'body' => 'Ordered a BPC-157 and TB-500 research combo from Axiom. Communication was excellent and all of my questions were answered quickly. Package arrived with proper packing and everything looked professional.',
        'experience_date' => 'January 22, 2026',
        'badge' => 'Unprompted review',
        'rating' => 5,
        'image' => '',
    ),
    array(
        'name' => 'Daniel R.',
        'country' => 'CA',
        'review_count' => '3 reviews',
        'date' => 'Jan 21, 2026',
        'title' => 'Clean ordering process and strong support',
        'body' => 'The site was easy to use, checkout was simple, and support responded fast when I had a question about a COA. Overall the experience felt much more polished than most research sites I have used.',
        'experience_date' => 'January 18, 2026',
        'badge' => 'Verified purchase',
        'rating' => 5,
        'image' => '',
    ),
    array(
        'name' => 'Aaron P.',
        'country' => 'US',
        'review_count' => '1 review',
        'date' => 'Jan 18, 2026',
        'title' => 'Fast USA delivery',
        'body' => 'Order shipped quickly and landed in a few days. Packaging was neat, labels looked professional, and the product page COA system made everything easier to verify.',
        'experience_date' => 'January 16, 2026',
        'badge' => 'Verified purchase',
        'rating' => 5,
        'image' => '',
    ),
);

function axiom_render_stars($count = 5) {
    $count = max(1, min(5, (int) $count));
    $html = '<div class="axiom-reviews-stars" aria-label="' . esc_attr($count . ' out of 5 stars') . '">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= '<span class="axiom-reviews-star' . ($i <= $count ? ' is-filled' : '') . '">★</span>';
    }
    $html .= '</div>';
    return $html;
}
?>

<main class="axiom-reviews-page">
  <section class="axiom-reviews-hero">
    <div class="container">
      <div class="axiom-reviews-hero-inner">
        <p class="axiom-reviews-kicker">Reviews & Trust</p>
        <h1><?php echo esc_html($reviews_page_data['brand_name']); ?></h1>
        <p class="axiom-reviews-subheadline"><?php echo esc_html($reviews_page_data['headline']); ?></p>
        <p class="axiom-reviews-site"><?php echo esc_html($reviews_page_data['brand_url']); ?></p>

        <div class="axiom-reviews-tags">
          <span>Peptides</span>
          <span>Research Compounds</span>
          <span>Third-Party Tested</span>
          <span>USA Fulfilled</span>
        </div>

        <div class="axiom-reviews-score-block">
          <div class="axiom-reviews-score-label">EXCELLENT</div>
          <?php echo axiom_render_stars(5); ?>
          <div class="axiom-reviews-score-number"><?php echo esc_html($reviews_page_data['trust_score']); ?></div>
          <div class="axiom-reviews-score-copy">based on <?php echo esc_html($reviews_page_data['total_reviews']); ?> reviews</div>
        </div>
      </div>
    </div>
  </section>

  <section class="axiom-reviews-summary">
    <div class="container">
      <div class="axiom-reviews-grid">
        <div class="axiom-reviews-card axiom-reviews-rating-card">
          <h2>Rating distribution</h2>

          <div class="axiom-reviews-distribution">
            <?php foreach ($reviews_page_data['rating_distribution'] as $star => $percent) : ?>
              <div class="axiom-reviews-distribution-row">
                <div class="axiom-reviews-distribution-label"><?php echo esc_html($star); ?> <span>★</span></div>
                <div class="axiom-reviews-distribution-bar">
                  <span style="width: <?php echo esc_attr((int) $percent); ?>%;"></span>
                </div>
                <div class="axiom-reviews-distribution-value"><?php echo esc_html((int) $percent); ?>%</div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="axiom-reviews-card axiom-reviews-about-card">
          <h2>About <?php echo esc_html($reviews_page_data['brand_name']); ?></h2>
          <p><?php echo esc_html($reviews_page_data['brand_name']); ?> is a research supply brand focused on high-quality laboratory compounds, strong communication, and a cleaner customer experience. This page highlights customer feedback, trust signals, and common mentions from repeat buyers.</p>

          <div class="axiom-reviews-meta-table">
            <div class="axiom-reviews-meta-row">
              <span>Company size</span>
              <strong><?php echo esc_html($reviews_page_data['company_size']); ?></strong>
            </div>
            <div class="axiom-reviews-meta-row">
              <span>Founded</span>
              <strong><?php echo esc_html($reviews_page_data['founded']); ?></strong>
            </div>
            <div class="axiom-reviews-meta-row">
              <span>TrustScore</span>
              <strong><?php echo esc_html($reviews_page_data['trust_score']); ?></strong>
            </div>
            <div class="axiom-reviews-meta-row">
              <span>Total Reviews</span>
              <strong><?php echo esc_html($reviews_page_data['total_reviews']); ?></strong>
            </div>
            <div class="axiom-reviews-meta-row">
              <span>Contact</span>
              <strong class="axiom-reviews-contact-links">
                <a href="<?php echo esc_url($reviews_page_data['telegram_url']); ?>" target="_blank" rel="noopener noreferrer">Telegram</a>
                <span>|</span>
                <a href="<?php echo esc_url($reviews_page_data['whatsapp_url']); ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
              </strong>
            </div>
          </div>
        </div>
      </div>

      <div class="axiom-reviews-card axiom-reviews-mentions-card">
        <div class="axiom-reviews-toolbar">
          <div>
            <h2>Top mentions</h2>
            <p>Common themes customers mention most often.</p>
          </div>
        </div>

        <div class="axiom-reviews-mentions">
          <?php foreach ($reviews_page_data['top_mentions'] as $mention) : ?>
            <span><?php echo esc_html($mention); ?></span>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="axiom-reviews-card axiom-reviews-notice">
        <p>Reviews on this page are presented as customer feedback and trust content for informational purposes. We do not offer incentives in exchange for positive reviews.</p>
      </div>
    </div>
  </section>

  <section class="axiom-reviews-list-section">
    <div class="container">
      <div class="axiom-reviews-list-header">
        <h2>Showing <?php echo esc_html(count($reviews_items)); ?> reviews</h2>
        <div class="axiom-reviews-sort">Most recent</div>
      </div>

      <div class="axiom-reviews-list">
        <?php foreach ($reviews_items as $review) : ?>
          <article class="axiom-review-card">
            <div class="axiom-review-top">
              <div class="axiom-review-avatar">
                <?php echo esc_html(strtoupper(substr($review['name'], 0, 1) . substr(strrchr(' ' . $review['name'], ' '), 1, 1))); ?>
              </div>

              <div class="axiom-review-author">
                <h3><?php echo esc_html($review['name']); ?></h3>
                <p><?php echo esc_html($review['country']); ?> · <?php echo esc_html($review['review_count']); ?></p>
              </div>

              <div class="axiom-review-date"><?php echo esc_html($review['date']); ?></div>
            </div>

            <?php echo axiom_render_stars($review['rating']); ?>

            <h4 class="axiom-review-title"><?php echo esc_html($review['title']); ?></h4>
            <div class="axiom-review-body">
              <p><?php echo esc_html($review['body']); ?></p>
            </div>

            <?php if (!empty($review['image'])) : ?>
              <div class="axiom-review-image">
                <img src="<?php echo esc_url($review['image']); ?>" alt="<?php echo esc_attr($review['title']); ?>">
              </div>
            <?php endif; ?>

            <div class="axiom-review-footer">
              <span class="axiom-review-pill">Date of experience: <?php echo esc_html($review['experience_date']); ?></span>
              <span class="axiom-review-pill"><?php echo esc_html($review['badge']); ?></span>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>
