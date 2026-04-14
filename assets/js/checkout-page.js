jQuery(function ($) {
  "use strict";

  var $body = $(document.body);
  var updateTimer = null;
  var couponUpdating = false;
  var couponLockedScroll = null;

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

  function showCouponMessage(message, type) {
    var $feedback = getCouponFeedbackBox();
    if (!$feedback.length) return;

    $feedback
      .removeClass("is-error is-success")
      .addClass(type === "success" ? "is-success" : "is-error")
      .html(message)
      .stop(true, true)
      .fadeIn(120);

    setTimeout(function () {
      restoreScrollPosition();
    }, 10);
  }

  function clearCouponMessage() {
    var $feedback = getCouponFeedbackBox();
    if (!$feedback.length) return;

    $feedback.removeClass("is-error is-success").hide().empty();
  }

  function clearTopNotices() {
    $(
      ".woocommerce-NoticeGroup," +
      ".woocommerce-error," +
      ".woocommerce-message," +
      ".woocommerce-info"
    ).remove();
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
      clearCouponMessage();
      clearTopNotices();

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
        .done(function (response) {
          clearTopNotices();

          var html = typeof response === "string" ? response : "";
          var $response = $("<div>").html(html);

          var errorText = $.trim(
            $response.find(".woocommerce-error li, .woocommerce-error").first().text()
          );

          var successText = $.trim(
            $response.find(".woocommerce-message, .woocommerce-info").first().text()
          );

          if (errorText) {
            showCouponMessage(errorText || "Discount code not found.", "error");
            couponUpdating = false;
            return;
          }

          showCouponMessage(successText || "Discount applied.", "success");
          $input.val("");

          setTimeout(function () {
            clearTopNotices();
            $body.trigger("update_checkout");
            restoreScrollPosition();
          }, 50);
        })
        .fail(function () {
          showCouponMessage("Could not apply discount code. Please try again.", "error");
          couponUpdating = false;
        })
        .always(function () {
          $form.removeClass("is-loading");
          $button.prop("disabled", false);
          clearTopNotices();
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

    if (couponUpdating) {
      restoreScrollPosition();
      couponUpdating = false;
    }
  });

  $body.on("checkout_error applied_coupon removed_coupon", function () {
    clearTopNotices();
    restoreScrollPosition();
  });

  bindAddressFieldEvents();
  bindShippingMethodEvents();
  bindCouponForm();

  setTimeout(function () {
    maybeUpdateCheckout();
  }, 500);

  setTimeout(function () {
    maybeUpdateCheckout();
  }, 1200);
});
