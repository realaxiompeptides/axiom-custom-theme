jQuery(function ($) {
  "use strict";

  var $body = $(document.body);
  var updateTimer = null;

  function queueCheckoutUpdate(delay) {
    clearTimeout(updateTimer);
    updateTimer = setTimeout(function () {
      $body.trigger("update_checkout");
    }, delay || 250);
  }

  function getFieldValue(selector) {
    var $field = $(selector);
    if (!$field.length) return "";
    return String($field.val() || "").trim();
  }

  function usingSeparateShippingAddress() {
    return $("#ship-to-different-address-checkbox").is(":checked");
  }

  function billingAddressComplete() {
    return (
      getFieldValue("#billing_country") !== "" &&
      getFieldValue("#billing_address_1") !== "" &&
      getFieldValue("#billing_city") !== "" &&
      getFieldValue("#billing_state") !== "" &&
      getFieldValue("#billing_postcode") !== ""
    );
  }

  function shippingAddressComplete() {
    return (
      getFieldValue("#shipping_country") !== "" &&
      getFieldValue("#shipping_address_1") !== "" &&
      getFieldValue("#shipping_city") !== "" &&
      getFieldValue("#shipping_state") !== "" &&
      getFieldValue("#shipping_postcode") !== ""
    );
  }

  function addressIsCompleteEnough() {
    if (usingSeparateShippingAddress()) {
      return shippingAddressComplete();
    }
    return billingAddressComplete();
  }

  function maybeUpdateCheckout() {
    if (addressIsCompleteEnough()) {
      queueCheckoutUpdate(250);
    }
  }

  function bindAddressFieldEvents() {
    var selectors = [
      "#billing_country",
      "#billing_address_1",
      "#billing_address_2",
      "#billing_city",
      "#billing_state",
      "#billing_postcode",
      "#billing_first_name",
      "#billing_last_name",
      "#billing_phone",
      "#billing_email",
      "#shipping_country",
      "#shipping_address_1",
      "#shipping_address_2",
      "#shipping_city",
      "#shipping_state",
      "#shipping_postcode",
      "#shipping_first_name",
      "#shipping_last_name",
      "#ship-to-different-address-checkbox"
    ].join(",");

    $(document).on("change", selectors, function () {
      maybeUpdateCheckout();
    });

    $(document).on("blur", selectors, function () {
      maybeUpdateCheckout();
    });

    $(document).on("input", selectors, function () {
      var id = this.id || "";
      if (
        id === "billing_postcode" ||
        id === "billing_city" ||
        id === "billing_address_1" ||
        id === "shipping_postcode" ||
        id === "shipping_city" ||
        id === "shipping_address_1"
      ) {
        maybeUpdateCheckout();
      }
    });
  }

  function bindShippingMethodEvents() {
    $(document).on("change", '.axiom-checkout-shipping-methods-fragment input.shipping_method, input.shipping_method', function () {
      queueCheckoutUpdate(100);
    });
  }

  $body.on("updated_checkout", function () {
    $(".axiom-checkout-shipping-methods-fragment input.shipping_method").each(function () {
      var $input = $(this);
      var name = $input.attr("name");
      var value = $input.val();

      if ($input.is(":checked")) {
        $('input[name="' + name + '"][value="' + value + '"]').prop("checked", true);
      }
    });
  });

  bindAddressFieldEvents();
  bindShippingMethodEvents();

  setTimeout(function () {
    maybeUpdateCheckout();
  }, 500);

  setTimeout(function () {
    maybeUpdateCheckout();
  }, 1200);
});
