document.addEventListener("DOMContentLoaded", function () {

    let tries = 0;

    let check = setInterval(() => {
        tries++;

        let gate =
            document.getElementById('ageGateOverlay') ||
            document.querySelector('.age-gate-overlay');

        let visible = gate && gate.offsetParent !== null;

        if (!visible || tries > 50) {
            clearInterval(check);

            if (!localStorage.getItem('axiom_popup_seen')) {
                setTimeout(() => {
                    document.getElementById('axiom-popup').style.display = 'block';
                }, 2500);
            }
        }
    }, 300);
});


function axiomNextStep() {
    let email = document.getElementById('axiom-email').value;

    if (!email.includes('@')) {
        alert('Enter valid email');
        return;
    }

    document.getElementById('axiom-step-email').style.display = 'none';
    document.getElementById('axiom-step-sms').style.display = 'block';
}


function axiomSubmitLead() {

    let email = document.getElementById('axiom-email').value;
    let phone = document.getElementById('axiom-phone').value;

    fetch(axiom_ajax.ajax_url, {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`action=axiom_save_lead&email=${encodeURIComponent(email)}&phone=${encodeURIComponent(phone)}`
    });

    document.getElementById('axiom-step-sms').style.display = 'none';
    document.getElementById('axiom-step-done').style.display = 'block';

    localStorage.setItem('axiom_popup_seen', '1');
}


function axiomClose() {
    document.getElementById('axiom-popup').style.display = 'none';
    localStorage.setItem('axiom_popup_seen', '1');
}
