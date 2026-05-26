(function ($) {
  'use strict';

  var CARD_PANEL_CLASS = 'axiom-card-upgrade';

  function normalizeText(value) {
    return String(value || '').replace(/\s+/g, ' ').trim().toLowerCase();
  }

  function getThemeUrl() {
    if (window.AXIOM_THEME && window.AXIOM_THEME.themeUrl) {
      return String(window.AXIOM_THEME.themeUrl).replace(/\/$/, '');
    }

    return '';
  }

  function isQuiklieCardMethod($method) {
    if (!$method || !$method.length) {
      return false;
    }

    var methodClass = normalizeText($method.attr('class'));
    var labelText = normalizeText($method.children('label').first().text());
    var inputValue = normalizeText($method.children('input.input-radio, input[type="radio"]').first().val());
    var imageSrc = '';

    $method.children('label').first().find('img').each(function () {
      imageSrc += ' ' + normalizeText($(this).attr('src'));
      imageSrc += ' ' + normalizeText($(this).attr('alt'));
    });

    return (
      methodClass.indexOf('quik') !== -1 ||
      methodClass.indexOf('quick') !== -1 ||
      methodClass.indexOf('quiklie') !== -1 ||
      methodClass.indexOf('qpay') !== -1 ||
      methodClass.indexOf('lupa') !== -1 ||

      labelText.indexOf('card') !== -1 ||
      labelText.indexOf('credit') !== -1 ||
      labelText.indexOf('debit') !== -1 ||
      labelText.indexOf('pay by card') !== -1 ||

      inputValue.indexOf('quik') !== -1 ||
      inputValue.indexOf('quick') !== -1 ||
      inputValue.indexOf('quiklie') !== -1 ||
      inputValue.indexOf('qpay') !== -1 ||
      inputValue.indexOf('lupa') !== -1 ||
      inputValue.indexOf('card') !== -1 ||

      imageSrc.indexOf('quik') !== -1 ||
      imageSrc.indexOf('quick') !== -1 ||
      imageSrc.indexOf('quiklie') !== -1 ||
      imageSrc.indexOf('lupa') !== -1
    );
  }

  function getCardPaymentMethod() {
    var $methods = $('.woocommerce-checkout #payment ul.payment_methods > li');

    var $matched = $methods.filter(function () {
      return isQuiklieCardMethod($(this));
    }).first();

    return $matched;
  }

  function buildCardPanel() {
    var themeUrl = getThemeUrl();
    var cardLogoUrl = themeUrl ? themeUrl + '/assets/images/card-checkout.PNG' : '';

    var logoHtml = cardLogoUrl
      ? '<img class="axiom-card-processor-logo" src="' + cardLogoUrl + '" alt="Secure card payment" loading="lazy">'
      : '<i class="fa-solid fa-credit-card"></i>';

    return $(
      [
        '<div class="' + CARD_PANEL_CLASS + '" data-axiom-card-upgrade="1">',

          '<div class="axiom-card-upgrade-head">',
            '<div class="axiom-card-upgrade-title">',
              '<span class="axiom-card-upgrade-icon" aria-hidden="true">',
                logoHtml,
              '</span>',
              '<div>',
                '<strong>Secure Card Checkout</strong>',
                '<span>Fast encrypted payment with instant authorization.</span>',
              '</div>',
            '</div>',
            '<span class="axiom-card-upgrade-badge">Card Payment</span>',
          '</div>',

          '<div class="axiom-card-brand-row" aria-label="Accepted card brands">',
            '<span class="axiom-card-brand axiom-card-brand-visa" aria-label="Visa">',
              '<i class="fa-brands fa-cc-visa"></i>',
            '</span>',
            '<span class="axiom-card-brand axiom-card-brand-mastercard" aria-label="Mastercard">',
              '<i class="fa-brands fa-cc-mastercard"></i>',
            '</span>',
            '<span class="axiom-card-brand axiom-card-brand-amex" aria-label="American Express">',
              '<i class="fa-brands fa-cc-amex"></i>',
            '</span>',
            '<span class="axiom-card-brand axiom-card-brand-discover" aria-label="Discover">',
              '<i class="fa-brands fa-cc-discover"></i>',
            '</span>',
          '</div>',

          '<div class="axiom-card-descriptor-box">',
            '<i class="fa-solid fa-circle-info"></i>',
            '<div>',
              '<strong>Important card payment notice</strong>',
              '<span>Charges on your card statement will appear as <b>LUPA GROUP</b>. Please ensure international payments are enabled on your card to avoid transaction failure.</span>',
            '</div>',
          '</div>',

          '<div class="axiom-card-mini-grid">',
            '<div class="axiom-card-mini-card">',
              '<i class="fa-solid fa-lock"></i>',
              '<strong>Encrypted checkout</strong>',
              '<span>Your payment details are processed securely.</span>',
            '</div>',

            '<div class="axiom-card-mini-card">',
              '<i class="fa-solid fa-bolt"></i>',
              '<strong>Instant authorization</strong>',
              '<span>Orders can move forward faster after approval.</span>',
            '</div>',

            '<div class="axiom-card-mini-card">',
              '<i class="fa-solid fa-shield-halved"></i>',
              '<strong>Bank-level security</strong>',
              '<span>Protected card submission through our processor.</span>',
            '</div>',
          '</div>',

        '</div>'
      ].join('')
    );
  }

  function removeDuplicateCardPanels($cardMethod) {
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

  function hideDefaultPluginCopy($cardMethod) {
    $cardMethod.find('.payment_box > p').each(function () {
      var $p = $(this);
      var text = normalizeText($p.text());

      if (
        text.indexOf('charges on your card statement') !== -1 ||
        text.indexOf('lupa group') !== -1 ||
        text.indexOf('international payments') !== -1 ||
        text.indexOf('transaction failure') !== -1 ||
        text.indexOf('card statement') !== -1
      ) {
        $p.addClass('axiom-card-default-copy-hidden');
      }
    });

    $cardMethod.find('.payment_box > img').addClass('axiom-card-default-icons-hidden');
    $cardMethod.find('.payment_box > .card-icons').addClass('axiom-card-default-icons-hidden');
    $cardMethod.find('.payment_box > .payment-icons').addClass('axiom-card-default-icons-hidden');
    $cardMethod.find('.payment_box > .accepted-cards').addClass('axiom-card-default-icons-hidden');
  }

  function addMethodClass($cardMethod) {
    if ($cardMethod.length) {
      $cardMethod.addClass('axiom-card-method-detected');
    }
  }

  function insertCardPanel() {
    var $cardMethod = getCardPaymentMethod();

    if (!$cardMethod.length) {
      $('.' + CARD_PANEL_CLASS).remove();
      return;
    }

    addMethodClass($cardMethod);
    removeDuplicateCardPanels($cardMethod);

    var $paymentBox = $cardMethod.children('.payment_box').first();

    if (!$paymentBox.length) {
      return;
    }

    if (!$paymentBox.children('.' + CARD_PANEL_CLASS).length) {
      $paymentBox.prepend(buildCardPanel());
    }

    hideDefaultPluginCopy($cardMethod);
  }

  function refreshCardPanel() {
    insertCardPanel();
  }

  $(document).ready(function () {
    refreshCardPanel();
  });

  $(document.body).on('updated_checkout payment_method_selected', function () {
    refreshCardPanel();
  });

  $(document).on('change', '.woocommerce-checkout #payment ul.payment_methods > li > input.input-radio, .woocommerce-checkout #payment ul.payment_methods > li > input[type="radio"]', function () {
    setTimeout(refreshCardPanel, 25);
  });

  setTimeout(refreshCardPanel, 250);
  setTimeout(refreshCardPanel, 750);
  setTimeout(refreshCardPanel, 1500);

})(jQuery);
