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
    return usingSeparateShippingAddress() ? shippingAddressComplete() : billingAddressComplete();
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

    $(document).on("change blur", selectors, function () {
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
    $(document).on(
      "change",
      '.axiom-checkout-shipping-methods-fragment input.shipping_method, #payment input.shipping_method, input.shipping_method',
      function () {
        queueCheckoutUpdate(100);
      }
    );
  }

  function getCouponFeedbackBox() {
    return $(".axiom-inline-coupon-feedback").first();
  }

  function showCouponMessage(message, type) {
    var $feedback = getCouponFeedbackBox();
    if (!$feedback.length) return;

    $feedback
      .removeClass("is-error is-success")
      .addClass(type === "success" ? "is-success" : "is-error")
      .html(message)
      .show();
  }

  function clearCouponMessage() {
    var $feedback = getCouponFeedbackBox();
    if (!$feedback.length) return;

    $feedback.removeClass("is-error is-success").empty().hide();
  }

  function clearTopCouponNotices() {
    $(".woocommerce-NoticeGroup, .woocommerce-error, .woocommerce-message, .woocommerce-info").remove();
  }

  function syncShippingRadios() {
    $(".axiom-checkout-shipping-methods-fragment input.shipping_method").each(function () {
      var $input = $(this);
      var name = $input.attr("name");
      var value = $input.val();

      if ($input.is(":checked")) {
        $('input[name="' + name + '"][value="' + value + '"]').prop("checked", true);
      }
    });
  }

  function bindCouponForm() {
    $(document).on("submit", ".axiom-inline-coupon-form", function (e) {
      e.preventDefault();

      clearCouponMessage();
      clearTopCouponNotices();

      var $form = $(this);
      var $input = $form.find('input[name="coupon_code"]');
      var couponCode = String($input.val() || "").trim();

      if (!couponCode) {
        showCouponMessage("Please enter a code.", "error");
        return;
      }

      if (
        typeof wc_checkout_params === "undefined" ||
        !wc_checkout_params.wc_ajax_url ||
        !wc_checkout_params.apply_coupon_nonce
      ) {
        showCouponMessage("Coupon system unavailable.", "error");
        return;
      }

      $form.addClass("is-loading");

      $.ajax({
        type: "POST",
        url: wc_checkout_params.wc_ajax_url.toString().replace("%%endpoint%%", "apply_coupon"),
        data: {
          security: wc_checkout_params.apply_coupon_nonce,
          coupon_code: couponCode
        }
      })
        .done(function (response) {
          var html = typeof response === "string" ? response : "";
          var $response = $("<div>").html(html);

          var errorText = $.trim(
            $response.find(".woocommerce-error li, .woocommerce-error").first().text()
          );

          var successText = $.trim(
            $response.find(".woocommerce-message, .woocommerce-info").first().text()
          );

          if (errorText) {
            showCouponMessage(errorText, "error");
            clearTopCouponNotices();
            return;
          }

          showCouponMessage(successText || "Code applied successfully.", "success");
          $input.val("");
          $body.trigger("update_checkout");
        })
        .fail(function () {
          showCouponMessage("Could not apply code. Please try again.", "error");
          clearTopCouponNotices();
        })
        .always(function () {
          $form.removeClass("is-loading");
        });
    });
  }

  $body.on("updated_checkout", function () {
    syncShippingRadios();
    clearTopCouponNotices();
  });

  $body.on("applied_coupon_in_checkout removed_coupon_in_checkout", function () {
    clearTopCouponNotices();
    queueCheckoutUpdate(100);
  });

  bindAddressFieldEvents();
  bindShippingMethodEvents();
  bindCouponForm();

  setTimeout(function () {
    maybeUpdateCheckout();
    clearTopCouponNotices();
  }, 500);

  setTimeout(function () {
    maybeUpdateCheckout();
    clearTopCouponNotices();
  }, 1200);
});
