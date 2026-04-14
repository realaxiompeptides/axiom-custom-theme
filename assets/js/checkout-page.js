jQuery(function ($) {
  "use strict";

  var $body = $(document.body);
  var updateTimer = null;
  var couponUpdating = false;
  var couponLockedScroll = null;
  var pendingCouponCode = "";
  var couponsBeforeApply = [];
  var COUPON_MESSAGE_KEY = "axiom_coupon_feedback_message";
  var COUPON_MESSAGE_TYPE_KEY = "axiom_coupon_feedback_type";

  if (typeof $.scroll_to_notices === "function") {
    $.scroll_to_notices = function () {
      return;
    };
  }

  if (typeof wc_checkout_form !== "undefined" && wc_checkout_form) {
    wc_checkout_form.scroll_to_notices = function () {
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

  function clearSavedCouponMessage() {
    try {
      sessionStorage.removeItem(COUPON_MESSAGE_KEY);
      sessionStorage.removeItem(COUPON_MESSAGE_TYPE_KEY);
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

  function showCouponMessage(message, type) {
    var $feedback = getCouponFeedbackBox();
    if (!$feedback.length) return;

    saveCouponMessage(message, type);

    $feedback
      .removeClass("is-error is-success")
      .addClass(type === "success" ? "is-success" : "is-error")
      .html(message)
      .show();

    setTimeout(function () {
      restoreScrollPosition();
    }, 10);
  }

  function restoreSavedCouponMessage() {
    var saved = readSavedCouponMessage();
    if (!saved.message) return;

    var $feedback = getCouponFeedbackBox();
    if (!$feedback.length) return;

    $feedback
      .removeClass("is-error is-success")
      .addClass(saved.type === "success" ? "is-success" : "is-error")
      .html(saved.message)
      .show();
  }

  function clearCouponMessage() {
    var $feedback = getCouponFeedbackBox();
    clearSavedCouponMessage();

    if (!$feedback.length) return;
    $feedback.removeClass("is-error is-success").empty().hide();
  }

  function clearTopNotices() {
    $(".woocommerce-NoticeGroup, .woocommerce-error, .woocommerce-message, .woocommerce-info").remove();
  }

  function getAppliedCouponCodes() {
    var codes = [];

    $(".axiom-applied-coupon-chip").each(function () {
      var text = $.trim($(this).text());
      if (text) {
        codes.push(text.toLowerCase());
      }
    });

    return codes;
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
        typeof wc_checkout_params === "undefined" ||
        !wc_checkout_params.wc_ajax_url ||
        !wc_checkout_params.apply_coupon_nonce
      ) {
        showCouponMessage("Coupon system unavailable.", "error");
        return false;
      }

      pendingCouponCode = couponCode.toLowerCase();
      couponsBeforeApply = getAppliedCouponCodes();

      couponUpdating = true;
      $form.addClass("is-loading");
      $button.prop("disabled", true);

      $.ajax({
        type: "POST",
        url: wc_checkout_params.wc_ajax_url.toString().replace("%%endpoint%%", "apply_coupon"),
        data: {
          security: wc_checkout_params.apply_coupon_nonce,
          coupon_code: couponCode
        }
      })
        .done(function () {
          clearTopNotices();
          $body.trigger("update_checkout");
        })
        .fail(function () {
          showCouponMessage("Could not apply discount code. Please try again.", "error");
          couponUpdating = false;
        })
        .always(function () {
          $form.removeClass("is-loading");
          $button.prop("disabled", false);
          restoreScrollPosition();
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
    $(".axiom-checkout-shipping-methods-fragment input.shipping_method").each(function () {
      var $input = $(this);
      var name = $input.attr("name");
      var value = $input.val();

      if ($input.is(":checked")) {
        $('input[name="' + name + '"][value="' + value + '"]').prop("checked", true);
      }
    });

    clearTopNotices();
    restoreSavedCouponMessage();

    if (couponUpdating) {
      var couponsAfterApply = getAppliedCouponCodes();
      var success =
        couponsAfterApply.length > couponsBeforeApply.length ||
        (pendingCouponCode && couponsAfterApply.indexOf(pendingCouponCode) !== -1);

      if (success) {
        showCouponMessage("Discount applied.", "success");
        $(".axiom-inline-coupon-input").val("");
      } else {
        showCouponMessage("Discount code not valid.", "error");
      }

      pendingCouponCode = "";
      couponsBeforeApply = [];
      couponUpdating = false;
      restoreScrollPosition();
    }
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
  }, 500);

  setTimeout(function () {
    maybeUpdateCheckout();
    restoreSavedCouponMessage();
  }, 1200);
});
