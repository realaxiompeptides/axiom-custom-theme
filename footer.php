<footer class="site-footer">
  <div class="footer-top">
    <div class="footer-container">
      <div class="footer-grid">
        <div class="footer-column footer-brand">
          <h2>Axiom Peptides</h2>
          <p>
            Axiom Peptides is a supplier of research-use compounds committed
            to delivering high-quality materials for laboratory and scientific
            purposes. Our products are tested for purity and consistency and
            are distributed strictly for research applications only.
          </p>
        </div>

        <div class="footer-column">
          <h3>Quick Links</h3>
          <ul class="footer-links">
            <li><a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')); ?>">Shop</a></li>
            <li><a href="<?php echo esc_url(home_url('/track-order/')); ?>">Track Order</a></li>
            <li><a href="<?php echo esc_url(home_url('/contact-us/')); ?>">Contact Us</a></li>
            <li><a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>">Privacy Policy</a></li>
            <li><a href="<?php echo esc_url(home_url('/terms-and-conditions/')); ?>">Terms &amp; Conditions</a></li>
            <li><a href="<?php echo esc_url(home_url('/refund-policy/')); ?>">Refund Policy</a></li>
            <li><a href="<?php echo esc_url(home_url('/shipping-policy/')); ?>">Shipping Policy</a></li>
            <li><a href="<?php echo esc_url(home_url('/research-disclaimer/')); ?>">Research Disclaimer</a></li>
          </ul>
        </div>

        <div class="footer-column">
          <h3>Get In Touch</h3>
          <p><strong>Email:</strong> realaxiompeptides@gmail.com</p>
          <p><strong>Business Address:</strong></p>
          <p>30 N Gould St # 61352</p>
          <p>Sheridan, WY 82801</p>
        </div>
      </div>

      <div class="footer-copy">
        © 2026 Axiom Peptides. All rights reserved.
      </div>
    </div>
  </div>

  <div class="footer-disclaimer">
    <div class="footer-container">
      <h3>FDA DISCLAIMER</h3>

      <p>
        The statements made within this website have not been evaluated by the
        United States Food and Drug Administration. The products offered by
        Axiom Peptides are not intended to diagnose, treat, cure, or prevent
        any disease. All products are for laboratory research use only and are
        not for human consumption.
      </p>

      <p>
        Axiom Peptides is a chemical supplier and is not a compounding pharmacy
        or registered outsourcing facility as defined by the Federal Food,
        Drug, and Cosmetic Act.
      </p>

      <p class="footer-disclaimer-strong">
        THE PRODUCTS WE OFFER ARE NOT INTENDED FOR HUMAN USE. THEY ARE
        INTENDED FOR IN-VITRO AND PRE-CLINICAL RESEARCH PURPOSES ONLY.
      </p>
    </div>
  </div>
</footer>

<div class="age-gate-overlay" id="ageGateOverlay" aria-hidden="true">
  <div class="age-gate-backdrop"></div>

  <div
    class="age-gate-modal"
    id="ageGateModal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="ageGateTitle"
    aria-describedby="ageGateDescription"
  >
    <div class="age-gate-logo-wrap">
      <img src="" alt="Axiom Peptides" class="age-gate-logo" id="ageGateLogo" />
    </div>

    <p class="age-gate-kicker">RESEARCH ACCESS NOTICE</p>
    <h1 class="age-gate-title" id="ageGateTitle">21+ Access Agreement</h1>

    <p class="age-gate-description" id="ageGateDescription">
      This website contains laboratory research products intended strictly for
      in-vitro research use only. By continuing, you confirm that you meet the
      age requirement and understand the nature of the materials presented on this site.
    </p>

    <div class="age-gate-points">
      <div class="age-gate-point"><i class="fa-solid fa-circle-check"></i><span>Research-use-only product catalog</span></div>
      <div class="age-gate-point"><i class="fa-solid fa-circle-check"></i><span>No human consumption or medical use</span></div>
      <div class="age-gate-point"><i class="fa-solid fa-circle-check"></i><span>Access limited to adults 21 years of age or older</span></div>
    </div>

    <div class="age-gate-checkboxes">
      <label class="age-gate-check">
        <input type="checkbox" id="ageGateAgeCheck" />
        <span>I confirm that I am <strong>21 years of age or older</strong>.</span>
      </label>

      <label class="age-gate-check">
        <input type="checkbox" id="ageGateUseCheck" />
        <span>I understand these materials are offered for <strong>in-vitro laboratory research only</strong> and not for human consumption.</span>
      </label>
    </div>

    <div class="age-gate-actions">
      <button type="button" class="age-gate-btn age-gate-btn-primary" id="ageGateEnterBtn" disabled>Enter Site</button>
      <button type="button" class="age-gate-btn age-gate-btn-secondary" id="ageGateExitBtn">Exit</button>
    </div>

    <p class="age-gate-footer">
      By entering, you agree to these conditions and acknowledge responsibility
      for compliance with your local laws and regulations.
    </p>
  </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
