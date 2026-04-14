jQuery(function ($) {
  "use strict";

  var $body = $(document.body);
  var updateTimer = null;

  function triggerCheckoutUpdate() {
    clearTimeout(updateTimer);
    updateTimer = setTimeout(function () {
      $body.trigger("update_checkout");
    }, 250);
  }

  function requiredAddressLooksComplete() {
    var country = $("#billing_country").val() || "";
    var address1 = $("#billing_address_1").val() || "";
    var city = $("#billing_city").val() || "";
    var state = $("#billing_state").val() || "";
    var postcode = $("#billing_postcode").val() || "";

    return (
      country.trim() !== "" &&
      address1.trim() !== "" &&
      city.trim() !== "" &&
      state.trim() !== "" &&
      postcode.trim() !== ""
    );
  }

  function maybeTriggerAddressUpdate() {
    if (requiredAddressLooksComplete()) {
      triggerCheckoutUpdate();
    }
  }

  function bindCheckoutRefresh() {
    var selectors = [
      "#billing_first_name",
      "#billing_last_name",
      "#billing_company",
      "#billing_country",
      "#billing_address_1",
      "#billing_address_2",
      "#billing_city",
      "#billing_state",
      "#billing_postcode",
      "#billing_phone",
      "#billing_email",
      "#ship-to-different-address-checkbox",
      "#shipping_first_name",
      "#shipping_last_name",
      "#shipping_company",
      "#shipping_country",
      "#shipping_address_1",
      "#shipping_address_2",
      "#shipping_city",
      "#shipping_state",
      "#shipping_postcode"
    ].join(",");

    $(document).on("change", selectors, function () {
      maybeTriggerAddressUpdate();
    });

    $(document).on("input blur", selectors, function () {
      maybeTriggerAddressUpdate();
    });

    $(document).on("change", 'input[name^="shipping_method["]', function () {
      triggerCheckoutUpdate();
    });
  }

  function keepCustomShippingBlockFresh() {
    $body.on("updated_checkout", function () {
      $(".axiom-checkout-shipping-methods-fragment input.shipping_method").off("change.axiomShipping");

      $(".axiom-checkout-shipping-methods-fragment input.shipping_method").on("change.axiomShipping", function () {
        var $input = $(this);
        var methodName = $input.attr("name");
        var methodValue = $input.val();

        $('input[name="' + methodName + '"]').prop("checked", false);
        $('input[name="' + methodName + '"][value="' + methodValue + '"]').prop("checked", true);

        triggerCheckoutUpdate();
      });
    });
  }

  bindCheckoutRefresh();
  keepCustomShippingBlockFresh();

  // Initial check in case fields are prefilled by browser/autofill
  setTimeout(function () {
    maybeTriggerAddressUpdate();
  }, 400);

  setTimeout(function () {
    maybeTriggerAddressUpdate();
  }, 1200);
});
