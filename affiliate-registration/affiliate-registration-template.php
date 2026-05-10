<?php
/*
Template Name: Affiliate Registration Template
*/
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$program_url = home_url('/affiliate-program/');
$account_url = home_url('/affiliate-account/');
?>

<main class="axiom-affiliate-page axiom-affiliate-registration-page">

    <section class="axiom-affiliate-registration-simple">
        <div class="axiom-affiliate-registration-container">

            <div class="axiom-affiliate-registration-header">
                <p class="axiom-affiliate-kicker">Affiliate Application</p>
                <h1>Create Partner Account</h1>
                <p>
                    Apply to become an Axiom affiliate. Choose your payment preference,
                    request your partner code, and tell us how you plan to promote Axiom.
                </p>
            </div>

            <div class="axiom-affiliate-registration-card">
                <div class="axiom-affiliate-form-wrap">
                    <?php echo do_shortcode('[slicewp_affiliate_registration]'); ?>
                </div>
            </div>

            <div class="axiom-affiliate-registration-footer-links">
                <a href="<?php echo esc_url($program_url); ?>">View affiliate program details</a>
                <span>•</span>
                <a href="<?php echo esc_url($account_url); ?>">Already approved? Log in</a>
            </div>

        </div>
    </section>

</main>

<?php get_footer(); ?>
