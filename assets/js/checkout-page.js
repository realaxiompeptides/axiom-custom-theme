jQuery(function ($) {
  "use strict";

  var $body = $(document.body);
  var updateTimer = null;
  var couponLockedScroll = null;
  var COUPON_MESSAGE_KEY = "axiom_coupon_feedback_message";
  var COUPON_MESSAGE_TYPE_KEY = "axiom_coupon_feedback_type";

  if (typeof $.scroll_to_notices === "function") {
    $.scroll_to_notices = function () {
      return;
    };
  }

  if (typeof window.wc_checkout_form !== "undefined" && window.wc_checkout_form) {
    window.wc_checkout_form.scroll_to_notices = function () {
      return;
    };
  }

  function queueCheckoutUpdate(delay) {
    clearTimeout(updateTimer);
    updateTimer = setTimeout(function () {
      $body.trigger("update_checkout");
    }, delay || 250);
  }

  function getFieldValue(selector) {
    var $field = $(selector);
    if (!$field.length) {
      return "";
    }
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

  function lockScrollPosition() {
    couponLockedScroll = window.pageYOffset || document.documentElement.scrollTop || 0;
  }

  function restoreScrollPosition() {
    if (couponLockedScroll !== null) {
      window.scrollTo(0, couponLockedScroll);
    }
  }

  function getCouponFeedbackBox() {
    return $(".axiom-inline-coupon-feedback").first();
  }

  function saveCouponMessage(message, type) {
    try {
      sessionStorage.setItem(COUPON_MESSAGE_KEY, message || "");
      sessionStorage.setItem(COUPON_MESSAGE_TYPE_KEY, type || "");
    } catch (e) {}
  }

  function readSavedCouponMessage() {
    try {
      return {
        message: sessionStorage.getItem(COUPON_MESSAGE_KEY) || "",
        type: sessionStorage.getItem(COUPON_MESSAGE_TYPE_KEY) || ""
      };
    } catch (e) {
      return { message: "", type: "" };
    }
  }

  function clearSavedCouponMessage() {
    try {
      sessionStorage.removeItem(COUPON_MESSAGE_KEY);
      sessionStorage.removeItem(COUPON_MESSAGE_TYPE_KEY);
    } catch (e) {}
  }

  function showCouponMessage(message, type) {
    var $feedback = getCouponFeedbackBox();
    if (!$feedback.length) {
      return;
    }

    saveCouponMessage(message, type);

    $feedback
      .removeClass("is-error is-success")
      .addClass(type === "success" ? "is-success" : "is-error")
      .text(message)
      .show();

    setTimeout(function () {
      restoreScrollPosition();
    }, 10);
  }

  function restoreSavedCouponMessage() {
    var saved = readSavedCouponMessage();
    var $feedback = getCouponFeedbackBox();

    if (!$feedback.length || !saved.message) {
      return;
    }

    $feedback
      .removeClass("is-error is-success")
      .addClass(saved.type === "success" ? "is-success" : "is-error")
      .text(saved.message)
      .show();
  }

  function clearCouponMessage() {
    var $feedback = getCouponFeedbackBox();
    clearSavedCouponMessage();

    if (!$feedback.length) {
      return;
    }

    $feedback.removeClass("is-error is-success").empty().hide();
  }

  function clearTopNotices() {
    $(
      ".woocommerce-NoticeGroup," +
      ".woocommerce-error," +
      ".woocommerce-message," +
      ".woocommerce-info"
    ).remove();
  }

  function syncShippingMethods() {
    $(".axiom-checkout-shipping-methods-fragment input.shipping_method").each(function () {
      var $input = $(this);
      var name = $input.attr("name");
      var value = $input.val();

      if ($input.is(":checked")) {
        $('input[name="' + name + '"][value="' + value + '"]').prop("checked", true);
      }
    });
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
      '.axiom-checkout-shipping-methods-fragment input.shipping_method, input.shipping_method',
      function () {
        queueCheckoutUpdate(100);
      }
    );
  }

  function bindCouponForm() {
    $(document).on("submit", ".axiom-inline-coupon-form", function (e) {
      e.preventDefault();
      e.stopPropagation();

      lockScrollPosition();
      clearTopNotices();
      clearCouponMessage();

      var $form = $(this);
      var $input = $form.find('input[name="coupon_code"]');
      var $button = $form.find(".axiom-inline-coupon-button");
      var couponCode = String($input.val() || "").trim();

      if (!couponCode) {
        showCouponMessage("Please enter a discount code.", "error");
        return false;
      }

      if (
        typeof AXIOM_CHECKOUT === "undefined" ||
        !AXIOM_CHECKOUT.ajaxUrl ||
        !AXIOM_CHECKOUT.applyCouponAction ||
        !AXIOM_CHECKOUT.applyCouponNonce
      ) {
        showCouponMessage("Coupon system unavailable.", "error");
        return false;
      }

      $form.addClass("is-loading");
      $button.prop("disabled", true);

      $.ajax({
        type: "POST",
        url: AXIOM_CHECKOUT.ajaxUrl,
        dataType: "json",
        data: {
          action: AXIOM_CHECKOUT.applyCouponAction,
          nonce: AXIOM_CHECKOUT.applyCouponNonce,
          coupon_code: couponCode
        }
      })
        .done(function (response) {
          clearTopNotices();

          if (!response || typeof response.success === "undefined") {
            showCouponMessage("Could not validate discount code. Please try again.", "error");
            return;
          }

          if (response.success) {
            showCouponMessage(
              (response.data && response.data.message) ? response.data.message : "Discount applied.",
              "success"
            );
            $input.val("");
          } else {
            showCouponMessage(
              (response.data && response.data.message) ? response.data.message : "Discount code not valid.",
              "error"
            );
          }

          setTimeout(function () {
            $body.trigger("update_checkout");
            restoreScrollPosition();
          }, 50);
        })
        .fail(function () {
          showCouponMessage("Could not apply discount code. Please try again.", "error");
        })
        .always(function () {
          $form.removeClass("is-loading");
          $button.prop("disabled", false);
          restoreScrollPosition();
          clearTopNotices();
        });

      return false;
    });

    $(document).on("click", ".axiom-inline-coupon-button", function (e) {
      e.preventDefault();
      e.stopPropagation();
      $(this).closest(".axiom-inline-coupon-form").trigger("submit");
      return false;
    });

    $(document).on("input", ".axiom-inline-coupon-input", function () {
      clearCouponMessage();
    });
  }

  $body.on("updated_checkout", function () {
    syncShippingMethods();
    clearTopNotices();
    restoreSavedCouponMessage();
    restoreScrollPosition();
  });

  $body.on("checkout_error applied_coupon removed_coupon", function () {
    clearTopNotices();
    restoreSavedCouponMessage();
    restoreScrollPosition();
  });

  bindAddressFieldEvents();
  bindShippingMethodEvents();
  bindCouponForm();

  setTimeout(function () {
    maybeUpdateCheckout();
    restoreSavedCouponMessage();
    clearTopNotices();
  }, 500);

  setTimeout(function () {
    maybeUpdateCheckout();
    restoreSavedCouponMessage();
    clearTopNotices();
  }, 1200);
});
