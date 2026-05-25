(function ($) {
  'use strict';

  function findBankPaymentMethod() {
    var $methods = $('.woocommerce-checkout #payment ul.payment_methods > li');

    return $methods.filter(function () {
      var $li = $(this);
      var labelText = $.trim($li.children('label').first().text()).toLowerCase();
      var classText = ($li.attr('class') || '').toLowerCase();

      return (
        labelText.indexOf('same-day bank payment') !== -1 ||
        labelText.indexOf('bank payment') !== -1 ||
        labelText.indexOf('pay by bank') !== -1 ||
        classText.indexOf('link') !== -1 ||
        classText.indexOf('bank') !== -1
      );
    }).first();
  }

  function upgradeBankPaymentBox() {
    var $bankMethod = findBankPaymentMethod();

    if (!$bankMethod.length) {
      return;
    }

    var $paymentBox = $bankMethod.find('.payment_box').first();

    if (!$paymentBox.length) {
      return;
    }

    if ($paymentBox.find('.axiom-bank-upgrade').length) {
      return;
    }

    var upgradeHtml = [
      '<div class="axiom-bank-upgrade">',
        '<div class="axiom-bank-upgrade-head">',
          '<div class="axiom-bank-upgrade-title">',
            '<span class="axiom-bank-upgrade-icon"><i class="fa-solid fa-building-columns"></i></span>',
            '<div>',
              '<strong>Pay by Bank — the fastest checkout option</strong>',
              '<span>Link your bank securely and complete checkout without entering card numbers.</span>',
            '</div>',
          '</div>',
          '<span class="axiom-bank-upgrade-badge">No card fees</span>',
        '</div>',

        '<div class="axiom-bank-upgrade-grid">',
          '<div class="axiom-bank-upgrade-card">',
            '<i class="fa-solid fa-bolt"></i>',
            '<strong>Instant confirmation</strong>',
            '<span>Orders process fast after payment confirmation.</span>',
          '</div>',

          '<div class="axiom-bank-upgrade-card">',
            '<i class="fa-solid fa-lock"></i>',
            '<strong>Bank-grade security</strong>',
            '<span>Your banking credentials are never shared with us.</span>',
          '</div>',

          '<div class="axiom-bank-upgrade-card">',
            '<i class="fa-solid fa-money-bill-wave"></i>',
            '<strong>0% card fees</strong>',
            '<span>Pay directly from your bank with no card entry.</span>',
          '</div>',

          '<div class="axiom-bank-upgrade-card">',
            '<i class="fa-solid fa-rotate"></i>',
            '<strong>One-tap next time</strong>',
            '<span>Save your bank for faster future checkout.</span>',
          '</div>',
        '</div>',

        '<div class="axiom-bank-save-row">',
          '<i class="fa-solid fa-circle-check"></i>',
          '<div>',
            '<strong>Save this bank for one-click checkout next time</strong>',
            '<span>A secure token is stored by our bank partner — never your login or account number.</span>',
          '</div>',
        '</div>',

        '<div class="axiom-bank-upgrade-foot">',
          '<span><i class="fa-solid fa-shield-halved"></i> 256-bit encryption</span>',
          '<span>Powered by Link Money</span>',
          '<span>12,000+ banks supported</span>',
        '</div>',
      '</div>'
    ].join('');

    $paymentBox.prepend(upgradeHtml);
  }

  function initBankUpgrade() {
    setTimeout(upgradeBankPaymentBox, 50);
  }

  $(document).ready(initBankUpgrade);

  $(document.body).on('updated_checkout payment_method_selected', function () {
    initBankUpgrade();
  });

  $(document).on('change', '.woocommerce-checkout #payment input[name="payment_method"]', function () {
    initBankUpgrade();
  });

})(jQuery);
