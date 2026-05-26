(function ($) {
  'use strict';

  var CARD_PANEL_CLASS = 'axiom-card-upgrade';

  function normalizeText(value) {
    return String(value || '').replace(/\s+/g, ' ').trim().toLowerCase();
  }

  function isCardPaymentMethod($method) {
    if (!$method || !$method.length) {
      return false;
    }

    var methodClass = normalizeText($method.attr('class'));
    var labelText = normalizeText($method.children('label').first().text());
    var inputValue = normalizeText($method.children('input.input-radio, input[type="radio"]').first().val());
    var labelImages = normalizeText($method.children('label').first().find('img').map(function () {
      return ($(this).attr('src') || '') + ' ' + ($(this).attr('alt') || '');
    }).get().join(' '));

    return (
      methodClass.indexOf('quik') !== -1 ||
      methodClass.indexOf('quick') !== -1 ||
      methodClass.indexOf('quiklie') !== -1 ||
      methodClass.indexOf('qpay') !== -1 ||
      methodClass.indexOf('lupa') !== -1 ||
      methodClass.indexOf('merchant') !== -1 ||
      methodClass.indexOf('card') !== -1 ||
      labelText.indexOf('pay by card') !== -1 ||
      labelText.indexOf('credit') !== -1 ||
      labelText.indexOf('debit') !== -1 ||
      inputValue.indexOf('quik') !== -1 ||
      inputValue.indexOf('quick') !== -1 ||
      inputValue.indexOf('quiklie') !== -1 ||
      inputValue.indexOf('lupa') !== -1 ||
      inputValue.indexOf('card') !== -1 ||
      labelImages.indexOf('quik') !== -1 ||
      labelImages.indexOf('quick') !== -1 ||
      labelImages.indexOf('lupa') !== -1
    );
  }

  function getCardPaymentMethod() {
    return $('.woocommerce-checkout #payment ul.payment_methods > li').filter(function () {
      return isCardPaymentMethod($(this));
    }).first();
  }

  function buildCardPanel() {
    return $(
      [
        '<div class="' + CARD_PANEL_CLASS + '" data-axiom-card-upgrade="1">',

          '<div class="axiom-card-head">',
            '<span class="axiom-card-icon" aria-hidden="true">',
              '<i class="fa-solid fa-credit-card"></i>',
            '</span>',
            '<div class="axiom-card-copy">',
              '<strong>Secure Card Checkout</strong>',
              '<span>Fast encrypted payment with instant authorization.</span>',
            '</div>',
          '</div>',

          '<div class="axiom-card-brands" aria-label="Accepted card brands">',
            '<span class="axiom-card-brand"><i class="fa-brands fa-cc-visa"></i></span>',
            '<span class="axiom-card-brand"><i class="fa-brands fa-cc-mastercard"></i></span>',
            '<span class="axiom-card-brand"><i class="fa-brands fa-cc-amex"></i></span>',
            '<span class="axiom-card-brand"><i class="fa-brands fa-cc-discover"></i></span>',
          '</div>',

          '<div class="axiom-card-notice">',
            '<i class="fa-solid fa-circle-info"></i>',
            '<div>',
              '<strong>Important card payment notice</strong>',
              '<span>Your card statement will show <b>LUPA GROUP</b>. Please make sure international transactions are enabled on your card before placing your order.</span>',
            '</div>',
          '</div>',

          '<div class="axiom-card-grid">',
            '<div class="axiom-card-benefit">',
              '<i class="fa-solid fa-lock"></i>',
              '<strong>Encrypted checkout</strong>',
              '<span>Your payment details are processed securely.</span>',
            '</div>',

            '<div class="axiom-card-benefit">',
              '<i class="fa-solid fa-bolt"></i>',
              '<strong>Instant authorization</strong>',
              '<span>Orders can move forward faster after approval.</span>',
            '</div>',

            '<div class="axiom-card-benefit">',
              '<i class="fa-solid fa-shield-halved"></i>',
              '<strong>Bank-level security</strong>',
              '<span>Protected card submission through our processor.</span>',
            '</div>',
          '</div>',

        '</div>'
      ].join('')
    );
  }

  function removeDuplicatePanels($cardMethod) {
    $('.' + CARD_PANEL_CLASS).each(function () {
      var $panel = $(this);

      if (!$cardMethod.length || !$panel.closest($cardMethod).length) {
        $panel.remove();
      }
    });

    var $panelsInside = $cardMethod.find('.' + CARD_PANEL_CLASS);

    if ($panelsInside.length > 1) {
      $panelsInside.slice(1).remove();
    }
  }

  function hideDefaultCardStuff($cardMethod) {
    var $paymentBox = $cardMethod.children('.payment_box').first();

    $paymentBox.children('p').each(function () {
      var $p = $(this);
      var text = normalizeText($p.text());

      if (
        text.indexOf('charges on your card') !== -1 ||
        text.indexOf('lupa group') !== -1 ||
        text.indexOf('international payments') !== -1 ||
        text.indexOf('card payment') !== -1 ||
        text.indexOf('secure card') !== -1
      ) {
        $p.addClass('axiom-card-default-hidden');
      }
    });

    $paymentBox.children('img, svg, picture').addClass('axiom-card-default-hidden');

    $paymentBox.find('img').each(function () {
      var src = normalizeText($(this).attr('src'));
      var alt = normalizeText($(this).attr('alt'));

      if (
        src.indexOf('visa') !== -1 ||
        src.indexOf('master') !== -1 ||
        src.indexOf('amex') !== -1 ||
        src.indexOf('discover') !== -1 ||
        alt.indexOf('visa') !== -1 ||
        alt.indexOf('master') !== -1 ||
        alt.indexOf('amex') !== -1 ||
        alt.indexOf('discover') !== -1
      ) {
        $(this).addClass('axiom-card-default-hidden');
      }
    });
  }

  function insertCardPanel() {
    var $cardMethod = getCardPaymentMethod();

    if (!$cardMethod.length) {
      $('.' + CARD_PANEL_CLASS).remove();
      return;
    }

    removeDuplicatePanels($cardMethod);

    var $paymentBox = $cardMethod.children('.payment_box').first();

    if (!$paymentBox.length) {
      return;
    }

    if (!$paymentBox.children('.' + CARD_PANEL_CLASS).length) {
      $paymentBox.prepend(buildCardPanel());
    }

    hideDefaultCardStuff($cardMethod);
  }

  function refreshCardPanel() {
    setTimeout(insertCardPanel, 20);
  }

  $(document).ready(function () {
    refreshCardPanel();
  });

  $(document.body).on('updated_checkout payment_method_selected', function () {
    refreshCardPanel();
  });

  $(document).on('change', '.woocommerce-checkout #payment ul.payment_methods > li > input.input-radio, .woocommerce-checkout #payment ul.payment_methods > li > input[type="radio"]', function () {
    refreshCardPanel();
  });

})(jQuery);
