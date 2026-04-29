<?php
if (!defined('ABSPATH')) exit;

/**
 * MAIN EMAIL SENDER
 */
function axiom_send_abandoned_cart_email($email, $step) {

    $subject = '';
    $message = '';
    $checkout_url = home_url('/checkout/');

    // EMAIL 1
    if ($step === 1) {
        $subject = 'You left something behind 👀';
        $message = axiom_email_template("
            Hey,<br><br>
            Looks like you left something in your cart.<br><br>
            <a href='{$checkout_url}' style='background:#3B6FE0;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;'>Return to Cart</a><br><br>
            – Axiom Peptides
        ");
    }

    // EMAIL 2
    if ($step === 2) {
        $subject = 'Still thinking it over?';
        $message = axiom_email_template("
            Hey,<br><br>
            Your items are still waiting.<br><br>
            • Third-party tested<br>
            • 99% purity<br>
            • Discreet shipping<br><br>
            <a href='{$checkout_url}' style='background:#3B6FE0;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;'>Complete Order</a><br><br>
            – Axiom Peptides
        ");
    }

    // EMAIL 3 (MONEY EMAIL)
    if ($step === 3) {
        $subject = 'Here’s 5% off';
        $message = axiom_email_template("
            Hey,<br><br>
            Use code <strong>AXIOM5</strong> for 5% off your order.<br><br>
            <a href='{$checkout_url}' style='background:#3B6FE0;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;'>Checkout Now</a><br><br>
            This won’t last long.<br><br>
            – Axiom Peptides
        ");
    }

    wp_mail($email, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
}

/**
 * EMAIL WRAPPER (BRANDING)
 */
function axiom_email_template($content) {
    return "
    <div
