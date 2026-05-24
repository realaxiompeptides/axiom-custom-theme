<?php
/*
Template Name: Affiliate Program Template
*/
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$registration_url = home_url('/affiliate-registration/');
$account_url      = home_url('/affiliate-account/');
$contact_url      = home_url('/contact-us/');
?>

<main class="axiom-affiliate-page axiom-affiliate-program-page">

    <section class="axap-hero">
        <div class="axap-shell">
            <div class="axap-hero-grid">

                <div class="axap-hero-copy axap-reveal">
                    <p class="axap-eyebrow">Axiom Affiliate Program</p>

                    <h1>Earn by referring researchers to Axiom.</h1>

                    <p class="axap-hero-text">
                        Join the Axiom partner program and earn commission from approved referred orders.
                        Get a referral link, affiliate dashboard, and the ability to request a custom partner code after approval.
                    </p>

                    <div class="axap-hero-actions">
                        <a class="axap-btn axap-btn-primary" href="<?php echo esc_url($registration_url); ?>">
                            Apply Now
                        </a>

                        <a class="axap-btn axap-btn-secondary" href="<?php echo esc_url($account_url); ?>">
                            Already approved? Log in
                        </a>
                    </div>

                    <div class="axap-proof-row">
                        <span><i class="fa-solid fa-percent"></i> Starts at 10%</span>
                        <span><i class="fa-solid fa-cookie-bite"></i> 30-day tracking</span>
                        <span><i class="fa-solid fa-chart-line"></i> Dashboard access</span>
                    </div>
                </div>

                <aside class="axap-hero-card axap-reveal">
                    <div class="axap-rating-line">
                        <span>Partner setup</span>
                        <strong>Simple</strong>
                    </div>

                    <div class="axap-big-percent">10%</div>
                    <p>Starting commission on approved referred sales.</p>

                    <div class="axap-mini-grid">
                        <div>
                            <strong>30 days</strong>
                            <span>Cookie window</span>
                        </div>
                        <div>
                            <strong>Code</strong>
                            <span>Request yours</span>
                        </div>
                    </div>

                    <a class="axap-card-link" href="<?php echo esc_url($registration_url); ?>">
                        Start application <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </aside>

            </div>
        </div>
    </section>

    <section class="axap-strip">
        <div class="axap-shell">
            <div class="axap-strip-grid axap-reveal">
                <div>
                    <i class="fa-solid fa-sack-dollar"></i>
                    <strong>10% Base Commission</strong>
                    <span>Starts at 10%. Higher rates may be offered to proven partners.</span>
                </div>

                <div>
                    <i class="fa-solid fa-clock"></i>
                    <strong>30-Day Tracking</strong>
                    <span>Referrals are tracked for 30 days after a qualified click.</span>
                </div>

                <div>
                    <i class="fa-solid fa-ticket"></i>
                    <strong>Custom Partner Code</strong>
                    <span>Request a clean code that matches your brand or audience.</span>
                </div>

                <div>
                    <i class="fa-solid fa-chart-simple"></i>
                    <strong>Affiliate Dashboard</strong>
                    <span>Track visits, referrals, commissions, and payout status.</span>
                </div>
            </div>
        </div>
    </section>

    <section class="axap-section">
        <div class="axap-shell">
            <div class="axap-two-col">

                <div class="axap-content axap-reveal">
                    <p class="axap-eyebrow">Why partner with us</p>
                    <h2>Built for serious affiliates, creators, and community owners.</h2>
                    <p>
                        Axiom is looking for partners who can promote cleanly, professionally, and compliantly.
                        This program is best for affiliates with real audiences, content pages, communities, or repeat traffic.
                    </p>

                    <div class="axap-feature-list">
                        <div class="axap-feature">
                            <div class="axap-feature-icon"><i class="fa-solid fa-link"></i></div>
                            <div>
                                <strong>Your own referral link</strong>
                                <span>Send traffic directly to Axiom and track referred orders from your dashboard.</span>
                            </div>
                        </div>

                        <div class="axap-feature">
                            <div class="axap-feature-icon"><i class="fa-solid fa-bullhorn"></i></div>
                            <div>
                                <strong>Easy to promote</strong>
                                <span>Use your link or partner code in posts, communities, messages, and content.</span>
                            </div>
                        </div>

                        <div class="axap-feature">
                            <div class="axap-feature-icon"><i class="fa-solid fa-user-check"></i></div>
                            <div>
                                <strong>Manual approval</strong>
                                <span>We review applicants so the program stays high quality and protected.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="axap-calculator axap-reveal" id="axapCalculator">
                    <div class="axap-calc-top">
                        <p class="axap-eyebrow">Earnings calculator</p>
                        <h3>Estimate your monthly potential</h3>
                        <span>Based on 10% starting commission.</span>
                    </div>

                    <div class="axap-calc-control">
                        <div class="axap-calc-label">
                            <span>New referrals per month</span>
                            <strong id="axapReferralsValue">10</strong>
                        </div>
                        <input id="axapReferrals" type="range" min="1" max="100" value="10">
                    </div>

                    <div class="axap-calc-control">
                        <div class="axap-calc-label">
                            <span>Average order value</span>
                            <strong id="axapAovValue">$250</strong>
                        </div>
                        <input id="axapAov" type="range" min="50" max="750" step="10" value="250">
                    </div>

                    <div class="axap-calc-control">
                        <div class="axap-calc-label">
                            <span>Estimated reorder rate</span>
                            <strong id="axapReorderValue">25%</strong>
                        </div>
                        <input id="axapReorder" type="range" min="0" max="80" step="5" value="25">
                    </div>

                    <div class="axap-calc-results">
                        <div>
                            <span>Projected monthly</span>
                            <strong id="axapMonthly">$313</strong>
                        </div>

                        <div>
                            <span>Projected first year</span>
                            <strong id="axapYearly">$3,750</strong>
                        </div>
                    </div>

                    <p class="axap-calc-note">
                        Estimate only. Actual earnings depend on traffic quality, approvals, order value, repeat orders, refunds, and compliance.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <section class="axap-section axap-dark-section">
        <div class="axap-shell">
            <div class="axap-center-head axap-reveal">
                <p class="axap-eyebrow">How it works</p>
                <h2>Apply, get approved, start referring.</h2>
            </div>

            <div class="axap-steps">
                <div class="axap-step axap-reveal">
                    <span>01</span>
                    <h3>Apply</h3>
                    <p>Submit your affiliate application and include your audience, website, social page, or community.</p>
                </div>

                <div class="axap-step axap-reveal">
                    <span>02</span>
                    <h3>Get approved</h3>
                    <p>After review, you receive affiliate access, tracking, and your partner dashboard.</p>
                </div>

                <div class="axap-step axap-reveal">
                    <span>03</span>
                    <h3>Share</h3>
                    <p>Use your referral link or request a custom partner code for clean promotion.</p>
                </div>

                <div class="axap-step axap-reveal">
                    <span>04</span>
                    <h3>Earn</h3>
                    <p>Earn commission from approved referred orders that follow program rules.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="axap-section">
        <div class="axap-shell">
            <div class="axap-two-col axap-two-col-reverse">

                <div class="axap-card axap-reveal">
                    <p class="axap-eyebrow">Fastest approvals</p>
                    <h2>Who we’re looking for</h2>

                    <div class="axap-check-list">
                        <div><i class="fa-solid fa-check"></i> Creators with real niche audiences</div>
                        <div><i class="fa-solid fa-check"></i> Community owners or group admins</div>
                        <div><i class="fa-solid fa-check"></i> Review sites, blogs, SEO pages, or content pages</div>
                        <div><i class="fa-solid fa-check"></i> Affiliates who understand compliant promotion</div>
                        <div><i class="fa-solid fa-check"></i> Partners who can drive repeat, high-quality buyers</div>
                    </div>
                </div>

                <div class="axap-card axap-reveal">
                    <p class="axap-eyebrow">Program rules</p>
                    <h2>Keep promotion compliant.</h2>

                    <div class="axap-rules-grid">
                        <div>
                            <strong>Research-use only</strong>
                            <span>Do not promote products for human use.</span>
                        </div>

                        <div>
                            <strong>No medical claims</strong>
                            <span>No treatment, cure, disease, or diagnosis claims.</span>
                        </div>

                        <div>
                            <strong>No spam traffic</strong>
                            <span>No misleading traffic, fake clicks, or low-quality spam.</span>
                        </div>

                        <div>
                            <strong>Approval required</strong>
                            <span>Accounts, referrals, and codes may be reviewed.</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="axap-section axap-faq-section">
        <div class="axap-shell">
            <div class="axap-center-head axap-reveal">
                <p class="axap-eyebrow">FAQ</p>
                <h2>Affiliate questions</h2>
            </div>

            <div class="axap-faq-grid">
                <details class="axap-faq axap-reveal" open>
                    <summary>What commission do affiliates start at?</summary>
                    <p>Affiliates start at 10% commission on approved referred sales. Higher rates may be offered to proven partners.</p>
                </details>

                <details class="axap-faq axap-reveal">
                    <summary>Can I request a custom partner code?</summary>
                    <p>Yes. You can request a custom code during signup or contact support after approval.</p>
                </details>

                <details class="axap-faq axap-reveal">
                    <summary>How long does tracking last?</summary>
                    <p>The standard affiliate cookie window is 30 days.</p>
                </details>

                <details class="axap-faq axap-reveal">
                    <summary>Can I promote with medical claims?</summary>
                    <p>No. Affiliates must avoid human-use, medical, treatment, disease, cure, or diagnosis claims.</p>
                </details>
            </div>
        </div>
    </section>

    <section class="axap-final-cta">
        <div class="axap-shell">
            <div class="axap-final-card axap-reveal">
                <p class="axap-eyebrow">Ready to partner?</p>
                <h2>Apply for the Axiom Affiliate Program.</h2>
                <p>Get your referral link, request a partner code, and start earning from approved referred orders.</p>

                <div class="axap-hero-actions axap-final-actions">
                    <a class="axap-btn axap-btn-primary" href="<?php echo esc_url($registration_url); ?>">
                        Apply Now
                    </a>

                    <a class="axap-btn axap-btn-secondary" href="<?php echo esc_url($contact_url); ?>">
                        Contact Support
                    </a>
                </div>
            </div>
        </div>
    </section>

    <a class="axap-mobile-sticky" href="<?php echo esc_url($registration_url); ?>">
        Apply to Earn 10%
    </a>

</main>

<?php get_footer(); ?>
