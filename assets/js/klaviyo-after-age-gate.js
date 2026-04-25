(function () {
  const KLAVIYO_FORM_ID = "PASTE_FORM_ID_HERE";
  const AGE_GATE_KEY = "axiom_age_gate_accepted_v1";
  const POPUP_SHOWN_KEY = "axiom_klaviyo_popup_shown_v1";

  function openKlaviyoForm() {
    if (!KLAVIYO_FORM_ID || KLAVIYO_FORM_ID === "PASTE_FORM_ID_HERE") return;
    if (sessionStorage.getItem(POPUP_SHOWN_KEY) === "true") return;

    window._klOnsite = window._klOnsite || [];
    window._klOnsite.push(["openForm", KLAVIYO_FORM_ID]);

    sessionStorage.setItem(POPUP_SHOWN_KEY, "true");
  }

  function schedulePopup() {
    setTimeout(openKlaviyoForm, 6000);
  }

  document.addEventListener("DOMContentLoaded", function () {
    if (localStorage.getItem(AGE_GATE_KEY) === "true") {
      schedulePopup();
      return;
    }

    const enterBtn = document.getElementById("ageGateEnterBtn");

    if (enterBtn) {
      enterBtn.addEventListener("click", function () {
        setTimeout(function () {
          if (localStorage.getItem(AGE_GATE_KEY) === "true") {
            schedulePopup();
          }
        }, 300);
      });
    }
  });
})();
