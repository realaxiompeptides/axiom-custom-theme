<?php
defined('ABSPATH') || exit;

$email_heading = isset($email_heading) ? $email_heading : 'Axiom Peptides';
$logo_url = get_template_directory_uri() . '/assets/images/axiom-menu-logo.PNG';
?>
<!DOCTYPE html>
<html>
<body style="margin:0;padding:0;background:#eef2f7;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f7;padding:28px 12px;">
<tr>
<td align="center">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#111827;border-radius:24px;overflow:hidden;border:1px solid #1e2b44;">
<tr>
<td align="center" style="padding:32px 28px 18px;">
    <div style="background:#ffffff;border-radius:999px;padding:12px 22px;display:inline-block;">
        <img src="<?php echo esc_url($logo_url); ?>" alt="Axiom Peptides" style="width:190px;max-width:190px;height:auto;display:block;">
    </div>
</td>
</tr>
<tr>
<td align="center" style="padding:8px 34px 18px;">
    <p style="margin:0 0 12px;color:#9db7ff;font-size:13px;font-weight:900;letter-spacing:.14em;text-transform:uppercase;">
        Axiom Peptides
    </p>
    <h1 style="margin:0;color:#ffffff;font-size:34px;line-height:1.15;font-weight:900;text-align:center;">
        <?php echo esc_html($email_heading); ?>
    </h1>
</td>
</tr>
<tr>
<td style="padding:0 34px 26px;color:#cbd5e1;font-size:16px;line-height:1.7;">
