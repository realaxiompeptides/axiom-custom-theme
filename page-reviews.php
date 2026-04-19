<?php
/*
Template Name: Axiom Reviews Page
*/
defined('ABSPATH') || exit;
get_header();

/*
 * Edit these values directly for now.
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
        <h2>Customer Reviews</h2>
        <div class="axiom-reviews-sort">Most recent</div>
      </div>

      <div class="axiom-reviews-card axiom-reviews-add-review-card">
        <div class="axiom-reviews-toolbar">
          <div>
            <h2>Add a Review</h2>
            <p>Share your experience with Axiom Peptides.</p>
          </div>
        </div>

        <div class="axiom-reviews-add-review-actions">
          <a href="#axiom-all-reviews" class="axiom-review-pill">Browse Reviews</a>
        </div>

        <div class="axiom-reviews-add-review-form">
          <?php echo do_shortcode('[WPCR_SHOW POSTID="ALL" NUM="1000" PAGINATE="1" PERPAGE="10" SHOWFORM="1" HIDEREVIEWS="1" HIDERESPONSE="0" SNIPPET="" MORE="" HIDECUSTOM="0"]'); ?>
        </div>
      </div>

      <div class="axiom-reviews-card axiom-reviews-all-card" id="axiom-all-reviews">
        <div class="axiom-reviews-toolbar">
          <div>
            <h2>All Reviews</h2>
            <p>Showing reviews from all products across the site.</p>
          </div>
        </div>

        <div class="axiom-plugin-reviews-wrap">
          <?php echo do_shortcode('[WPCR_SHOW POSTID="ALL" NUM="1000" PAGINATE="1" PERPAGE="10" SHOWFORM="0" HIDEREVIEWS="0" HIDERESPONSE="0" SNIPPET="" MORE="" HIDECUSTOM="0"]'); ?>
        </div>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>
