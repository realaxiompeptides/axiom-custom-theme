(function ($) {
  'use strict';

  var ZELLE_PANEL_CLASS = 'axiom-zelle-upgrade';

  function normalizeText(value) {
    return String(value || '').replace(/\s+/g, ' ').trim().toLowerCase();
  }

  function getThemeUrl() {
    if (typeof AXIOM_THEME !== 'undefined' && AXIOM_THEME.themeUrl) {
      return AXIOM_THEME.themeUrl.replace(/\/$/, '');
    }

    return '';
  }

  function getZelleIconUrl() {
    return getThemeUrl() + '/assets/images/zelle-checkout.JPG';
  }

  function isZellePaymentMethod($method) {
    if (!$method || !$method.length) {
      return false;
    }

    var methodClass = normalizeText($method.attr('class'));
    var labelText = normalizeText($method.children('label').first().text());
    var inputValue = normalizeText(
      $method.children('input.input-radio, input[type="radio"]').first().val()
    );

    return (
      methodClass.indexOf('zelle') !== -1 ||
      labelText.indexOf('zelle') !== -1 ||
      inputValue.indexOf('zelle') !== -1
    );
  }

  function getZellePaymentMethod() {
    return $('.woocommerce-checkout #payment ul.payment_methods > li').filter(function () {
      return isZellePaymentMethod($(this));
    }).first();
  }

  function buildZellePanel() {
    var zelleIconUrl = getZelleIconUrl();

    return $(
      [
        '<div class="' + ZELLE_PANEL_CLASS + '" data-axiom-zelle-upgrade="1">',
          '<div class="axiom-zelle-head">',
            '<div class="axiom-zelle-title">',
              '<span class="axiom-zelle-icon" aria-hidden="true">',
                '<img class="axiom-zelle-icon-image" src="' + zelleIconUrl + '" alt="Zelle" />',
              '</span>',
              '<div>',
                '<strong>Zelle Payment</strong>',
                '<span>Send payment after checkout using your order number only.</span>',
              '</div>',
            '</div>',
            '<span class="axiom-zelle-badge">5% Discount</span>',
          '</div>',

          '<div class="axiom-zelle-highlight">',
            '<i class="fa-solid fa-circle-check"></i>',
            '<div>',
              '<strong>Automatic 5% order discount</strong>',
              '<span>Your Zelle discount is applied at checkout when this payment option is selected.</span>',
            '</div>',
          '</div>',

          '<div class="axiom-zelle-grid">',
            '<div class="axiom-zelle-card">',
              '<i class="fa-solid fa-receipt"></i>',
              '<strong>Order number required</strong>',
              '<span>Use your order number only so your payment can be matched quickly.</span>',
            '</div>',

            '<div class="axiom-zelle-card">',
              '<i class="fa-solid fa-clock"></i>',
              '<strong>Fast confirmation</strong>',
              '<span>Orders are processed once payment is confirmed.</span>',
            '</div>',

            '<div class="axiom-zelle-card">',
              '<i class="fa-solid fa-shield-halved"></i>',
              '<strong>Secure transfer</strong>',
              '<span>Send directly through your own banking app.</span>',
            '</div>',
          '</div>',

          '<div class="axiom-zelle-note">',
            '<i class="fa-solid fa-circle-info"></i>',
            '<span>Payment details are shown after placing your order. Please do not include product names in the memo.</span>',
          '</div>',
        '</div>'
      ].join('')
    );
  }

  function removeDuplicateZellePanels($zelleMethod) {
    $('.' + ZELLE_PANEL_CLASS).each(function () {
      var $panel = $(this);

      if (!$zelleMethod.length || !$panel.closest($zelleMethod).length) {
        $panel.remove();
      }
    });

    var $panelsInside = $zelleMethod.find('.' + ZELLE_PANEL_CLASS);

    if ($panelsInside.length > 1) {
      $panelsInside.slice(1).remove();
    }
  }

  function hideDefaultZelleCopy($zelleMethod) {
    $zelleMethod.find('.payment_box > p').each(function () {
      var $p = $(this);
      var text = normalizeText($p.text());

      if (
        text.indexOf('zelle') !== -1 ||
        text.indexOf('send payment') !== -1 ||
        text.indexOf('payment after checkout') !== -1 ||
        text.indexOf('order number') !== -1 ||
        text.indexOf('discount') !== -1
      ) {
        $p.addClass('axiom-zelle-default-copy-hidden');
      }
    });
  }

  function insertZellePanel() {
    var $zelleMethod = getZellePaymentMethod();

    if (!$zelleMethod.length) {
      $('.' + ZELLE_PANEL_CLASS).remove();
      return;
    }

    removeDuplicateZellePanels($zelleMethod);

    var $paymentBox = $zelleMethod.children('.payment_box').first();

    if (!$paymentBox.length) {
      return;
    }

    if (!$paymentBox.children('.' + ZELLE_PANEL_CLASS).length) {
      $paymentBox.prepend(buildZellePanel());
    }

    hideDefaultZelleCopy($zelleMethod);
  }

  function refreshZellePanel() {
    insertZellePanel();
  }

  $(document).ready(function () {
    refreshZellePanel();
  });

  $(document.body).on('updated_checkout payment_method_selected', function () {
    refreshZellePanel();
  });

  $(document).on(
    'change',
    '.woocommerce-checkout #payment ul.payment_methods > li > input.input-radio, .woocommerce-checkout #payment ul.payment_methods > li > input[type="radio"]',
    function () {
      setTimeout(refreshZellePanel, 25);
    }
  );

})(jQuery);
