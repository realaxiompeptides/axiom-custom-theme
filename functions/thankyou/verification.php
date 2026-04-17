<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!isset($order) || !$order instanceof WC_Order) {
    return;
}
?>

<section class="axiom-order-verify-card">
    <div class="axiom-order-verify-inner">
        <p class="axiom-order-verify-kicker">Order Access Required</p>
        <h1 class="axiom-order-verify-title">Verify Your Order</h1>
        <p class="axiom-order-verify-copy">
            Enter the email address used at checkout to view your order details.
        </p>

        <?php wc_print_notices(); ?>

        <form method="post" class="axiom-order-verify-form">
            <p class="axiom-order-verify-field">
                <label for="order_email">Email address <span>*</span></label>
                <input
                    type="email"
                    name="order_email"
                    id="order_email"
                    required
                    autocomplete="email"
                />
            </p>

            <input type="hidden" name="order_id" value="<?php echo esc_attr($order->get_id()); ?>">
            <input type="hidden" name="order_key" value="<?php echo esc_attr($order->get_order_key()); ?>">
            <input type="hidden" name="axiom_verify_order" value="1">

            <button type="submit" class="axiom-order-verify-button">Verify</button>
        </form>
    </div>
</section>

<style>
.axiom-order-verify-card{
  max-width:920px;
  margin:32px auto;
  padding:24px;
  border:1px solid #dbe5ef;
  border-radius:28px;
  background:#ffffff;
}
.axiom-order-verify-inner{
  max-width:680px;
  margin:0 auto;
}
.axiom-order-verify-kicker{
  margin:0 0 10px;
  color:#5aa8df;
  font-size:13px;
  font-weight:900;
  letter-spacing:.14em;
  text-transform:uppercase;
}
.axiom-order-verify-title{
  margin:0 0 12px;
  color:#0f172a;
  font-size:40px;
  line-height:1.05;
  font-weight:900;
}
.axiom-order-verify-copy{
  margin:0 0 20px;
  color:#64748b;
  font-size:18px;
  line-height:1.7;
}
.axiom-order-verify-form{
  display:grid;
  gap:14px;
}
.axiom-order-verify-field{
  margin:0;
}
.axiom-order-verify-field label{
  display:block;
  margin-bottom:10px;
  color:#0f172a;
  font-size:15px;
  font-weight:800;
}
.axiom-order-verify-field input{
  width:100%;
  min-height:58px;
  padding:0 18px;
  border:1px solid #d5e1ec;
  border-radius:18px;
  background:#fbfdff;
  color:#0f172a;
  font-size:18px;
  box-sizing:border-box;
}
.axiom-order-verify-button{
  min-height:56px;
  padding:0 22px;
  border:0;
  border-radius:999px;
  background:linear-gradient(135deg,#4aa7e8,#2f84bf);
  color:#ffffff;
  font-size:18px;
  font-weight:900;
  cursor:pointer;
}
@media (max-width:767px){
  .axiom-order-verify-card{
    margin:20px auto;
    padding:18px;
    border-radius:24px;
  }
  .axiom-order-verify-title{
    font-size:32px;
  }
  .axiom-order-verify-copy{
    font-size:16px;
  }
}
</style>
