(function ($) {
  'use strict';

  var BANK_PANEL_CLASS = 'axiom-bank-upgrade';
  var BANK_METHOD_KEYWORDS = [
    'same-day bank payment',
    'same day bank payment',
    'pay by bank',
    'link money',
    'link.money',
    'bank payment'
  ];

  function normalizeText(value) {
    return String(value || '').replace(/\s+/g, ' ').trim().toLowerCase();
  }

  function isBankPaymentMethod($method) {
    if (!$method || !$method.length) {
      return false;
    }

    var methodClass = normalizeText($method.attr('class'));
    var labelText = normalizeText($method.children('label').first().text());
    var inputValue = normalizeText($method.children('input.input-radio, input[type="radio"]').first().val());

    if (
      methodClass.indexOf('link') !== -1 ||
      methodClass.indexOf('bank') !== -1 ||
      inputValue.indexOf('link') !== -1 ||
      inputValue.indexOf('bank') !== -1
    ) {
      return true;
    }

    for (var i = 0; i < BANK_METHOD_KEYWORDS.length; i++) {
      if (labelText.indexOf(BANK_METHOD_KEYWORDS[i]) !== -1) {
        return true;
      }
    }

    return false;
  }

  function getBankPaymentMethod() {
    var $methods = $('.woocommerce-checkout #payment ul.payment_methods > li');

    var $match = $methods.filter(function () {
      return isBankPaymentMethod($(this));
    }).first();

    return $match;
  }

  function buildBankPanel() {
    return $(
      [
        '<div class="' + BANK_PANEL_CLASS + '" data-axiom-bank-upgrade="1">',
          '<div class="axiom-bank-upgrade-head">',
            '<div class="axiom-bank-upgrade-title">',
              '<span class="axiom-bank-upgrade-icon" aria-hidden="true">',
                '<i class="fa-solid fa-building-columns"></i>',
              '</span>',
              '<div>',
                '<strong>Pay by Bank</strong>',
                '<span>Secure bank checkout with instant confirmation and zero payment fees.</span>',
              '</div>',
            '</div>',
            '<span class="axiom-bank-upgrade-badge">0% Fees</span>',
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
              '<strong>0% fees</strong>',
              '<span>Pay directly from your bank with no payment fee.</span>',
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
      ].join('')
    );
  }

  function removeDuplicateBankPanels($bankMethod) {
    $('.' + BANK_PANEL_CLASS).each(function () {
      var $panel = $(this);

      if (!$bankMethod.length || !$panel.closest($bankMethod).length) {
        $panel.remove();
      }
    });

    var $panelsInside = $bankMethod.find('.' + BANK_PANEL_CLASS);

    if ($panelsInside.length > 1) {
      $panelsInside.slice(1).remove();
    }
  }

  function hideDefaultBankParagraph($bankMethod) {
    $bankMethod.find('.payment_box > p').each(function () {
      var $p = $(this);
      var text = normalizeText($p.text());

      if (
        text.indexOf('pay directly from your bank') !== -1 ||
        text.indexOf('no cards needed') !== -1 ||
        text.indexOf('secure verification') !== -1 ||
        text.indexOf('banking app') !== -1 ||
        text.indexOf('orders process immediately') !== -1
      ) {
        $p.addClass('axiom-bank-default-copy-hidden');
      }
    });
  }

  function insertBankPanel() {
    var $bankMethod = getBankPaymentMethod();

    if (!$bankMethod.length) {
      $('.' + BANK_PANEL_CLASS).remove();
      return;
    }

    removeDuplicateBankPanels($bankMethod);

    var $paymentBox = $bankMethod.children('.payment_box').first();

    if (!$paymentBox.length) {
      return;
    }

    if (!$paymentBox.children('.' + BANK_PANEL_CLASS).length) {
      $paymentBox.prepend(buildBankPanel());
    }

    hideDefaultBankParagraph($bankMethod);
  }

  function updateSelectedPaymentClass() {
    $('.woocommerce-checkout #payment ul.payment_methods > li').removeClass('axiom-payment-is-selected');

    $('.woocommerce-checkout #payment ul.payment_methods > li').each(function () {
      var $method = $(this);
      var $directRadio = $method.children('input.input-radio, input[type="radio"]').first();

      if ($directRadio.length && $directRadio.is(':checked')) {
        $method.addClass('axiom-payment-is-selected');
      }
    });
  }

  function refreshAxiomCheckoutBankPayment() {
    insertBankPanel();
    updateSelectedPaymentClass();
  }

  $(document).ready(function () {
    refreshAxiomCheckoutBankPayment();
  });

  $(document.body).on('updated_checkout payment_method_selected', function () {
    refreshAxiomCheckoutBankPayment();
  });

  $(document).on('change', '.woocommerce-checkout #payment ul.payment_methods > li > input.input-radio, .woocommerce-checkout #payment ul.payment_methods > li > input[type="radio"]', function () {
    setTimeout(refreshAxiomCheckoutBankPayment, 25);
  });

})(jQuery);
