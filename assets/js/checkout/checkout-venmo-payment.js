(function ($) {
  'use strict';

  var VENMO_PANEL_CLASS = 'axiom-venmo-upgrade';

  function normalizeText(value) {
    return String(value || '').replace(/\s+/g, ' ').trim().toLowerCase();
  }

  function getThemeUrl() {
    if (typeof AXIOM_THEME !== 'undefined' && AXIOM_THEME && AXIOM_THEME.themeUrl) {
      return AXIOM_THEME.themeUrl;
    }

    return window.location.origin + '/wp-content/themes/axiom-custom-theme';
  }

  function getVenmoIconUrl() {
    return getThemeUrl() + '/assets/images/venmo-checkout.PNG';
  }

  function isVenmoPaymentMethod($method) {
    if (!$method || !$method.length) {
      return false;
    }

    var methodClass = normalizeText($method.attr('class'));
    var labelText = normalizeText($method.children('label').first().text());
    var inputValue = normalizeText($method.children('input.input-radio, input[type="radio"]').first().val());

    return (
      methodClass.indexOf('venmo') !== -1 ||
      labelText.indexOf('venmo') !== -1 ||
      inputValue.indexOf('venmo') !== -1
    );
  }

  function getVenmoPaymentMethod() {
    return $('.woocommerce-checkout #payment ul.payment_methods > li').filter(function () {
      return isVenmoPaymentMethod($(this));
    }).first();
  }

  function buildVenmoPanel() {
    var venmoIconUrl = getVenmoIconUrl();

    return $(
      [
        '<div class="' + VENMO_PANEL_CLASS + '" data-axiom-venmo-upgrade="1">',
          '<div class="axiom-venmo-head">',
            '<div class="axiom-venmo-title">',
              '<span class="axiom-venmo-icon" aria-hidden="true">',
                '<img class="axiom-venmo-icon-image" src="' + venmoIconUrl + '" alt="">',
              '</span>',
              '<div>',
                '<strong>Venmo Payment</strong>',
                '<span>Send payment after checkout using your order number only.</span>',
              '</div>',
            '</div>',
            '<span class="axiom-venmo-badge">Fast Mobile Pay</span>',
          '</div>',

          '<div class="axiom-venmo-highlight">',
            '<i class="fa-solid fa-mobile-screen-button"></i>',
            '<div>',
              '<strong>Quick mobile checkout</strong>',
              '<span>Place your order first, then complete payment through Venmo using the instructions shown after checkout.</span>',
            '</div>',
          '</div>',

          '<div class="axiom-venmo-grid">',
            '<div class="axiom-venmo-card">',
              '<i class="fa-solid fa-receipt"></i>',
              '<strong>Order number required</strong>',
              '<span>Use your order number only so your payment can be matched quickly.</span>',
            '</div>',

            '<div class="axiom-venmo-card">',
              '<i class="fa-solid fa-bolt"></i>',
              '<strong>Simple confirmation</strong>',
              '<span>Your order is processed once payment is confirmed.</span>',
            '</div>',

            '<div class="axiom-venmo-card">',
              '<i class="fa-solid fa-shield-halved"></i>',
              '<strong>Secure transfer</strong>',
              '<span>Pay directly through your Venmo account after placing the order.</span>',
            '</div>',
          '</div>',

          '<div class="axiom-venmo-note">',
            '<i class="fa-solid fa-circle-info"></i>',
            '<span>Payment details are shown after placing your order. Please do not include product names in the memo.</span>',
          '</div>',
        '</div>'
      ].join('')
    );
  }

  function removeDuplicateVenmoPanels($venmoMethod) {
    $('.' + VENMO_PANEL_CLASS).each(function () {
      var $panel = $(this);

      if (!$venmoMethod.length || !$panel.closest($venmoMethod).length) {
        $panel.remove();
      }
    });

    var $panelsInside = $venmoMethod.find('.' + VENMO_PANEL_CLASS);

    if ($panelsInside.length > 1) {
      $panelsInside.slice(1).remove();
    }
  }

  function hideDefaultVenmoCopy($venmoMethod) {
    $venmoMethod.find('.payment_box > p').each(function () {
      var $p = $(this);
      var text = normalizeText($p.text());

      if (
        text.indexOf('venmo') !== -1 ||
        text.indexOf('send payment') !== -1 ||
        text.indexOf('payment after checkout') !== -1 ||
        text.indexOf('order number') !== -1 ||
        text.indexOf('mobile payment') !== -1 ||
        text.indexOf('fast and easy') !== -1
      ) {
        $p.addClass('axiom-venmo-default-copy-hidden');
      }
    });
  }

  function insertVenmoPanel() {
    var $venmoMethod = getVenmoPaymentMethod();

    if (!$venmoMethod.length) {
      $('.' + VENMO_PANEL_CLASS).remove();
      return;
    }

    removeDuplicateVenmoPanels($venmoMethod);

    var $paymentBox = $venmoMethod.children('.payment_box').first();

    if (!$paymentBox.length) {
      return;
    }

    if (!$paymentBox.children('.' + VENMO_PANEL_CLASS).length) {
      $paymentBox.prepend(buildVenmoPanel());
    }

    hideDefaultVenmoCopy($venmoMethod);
  }

  function refreshVenmoPanel() {
    insertVenmoPanel();
  }

  $(document).ready(function () {
    refreshVenmoPanel();
  });

  $(document.body).on('updated_checkout payment_method_selected', function () {
    refreshVenmoPanel();
  });

  $(document).on('change', '.woocommerce-checkout #payment ul.payment_methods > li > input.input-radio, .woocommerce-checkout #payment ul.payment_methods > li > input[type="radio"]', function () {
    setTimeout(refreshVenmoPanel, 25);
  });

})(jQuery);
