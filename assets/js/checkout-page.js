document.addEventListener("DOMContentLoaded", function () {
  if (typeof jQuery === "undefined") return;

  const $ = jQuery;
  const $body = $(document.body);
  const $form = $("form.checkout");

  if (!$form.length) return;

  let updateTimer = null;

  function queueCheckoutUpdate(delay = 250) {
    clearTimeout(updateTimer);
    updateTimer = setTimeout(function () {
      $body.trigger("update_checkout");
    }, delay);
  }

  const watchedSelector = [
    "#billing_country",
    "#billing_state",
    "#billing_city",
    "#billing_postcode",
    "#billing_address_1",
    "#billing_address_2",
    "#shipping_country",
    "#shipping_state",
    "#shipping_city",
    "#shipping_postcode",
    "#shipping_address_1",
    "#shipping_address_2",
    "#ship-to-different-address-checkbox",
    "input.shipping_method",
    "select.shipping_method"
  ].join(",");

  $(document).on("change", watchedSelector, function () {
    queueCheckoutUpdate(150);
  });

  $(document).on(
    "input blur keyup",
    "#billing_city, #billing_postcode, #billing_address_1, #shipping_city, #shipping_postcode, #shipping_address_1",
    function () {
      queueCheckoutUpdate(350);
    }
  );

  $(document).on("updated_checkout", function () {
    // keeps custom shipping section refreshing after WooCommerce swaps fragments
  });

  // initial sync after page load
  setTimeout(function () {
    $body.trigger("update_checkout");
  }, 500);
});
