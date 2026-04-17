<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Custom order verification form
 *
 * Expected variables:
 * $order
 */
if (!isset($order) || !$order instanceof WC_Order) {
    return;
}

$order_id = $order->get_id();
?>

<section class="axiom-order-verify-card">
    <div class="axiom-order-verify-inner">
        <div class="axiom-order-verify-kicker">Order Access Required</div>
        <h1 class="axiom-order-verify-title">Verify Your Order</h1>
        <p class="axiom-order-verify-copy">
            To view this order, enter the email address used at checkout. Once verified, you’ll be able to access the full order details page.
        </p>

        <form method="post" class="axiom-order-verify-form">
            <input type="hidden" name="check_submission" value="1" />
            <input type="hidden" name="order_id" value="<?php echo esc_attr($order_id); ?>" />
            <input type="hidden" name="order_key" value="<?php echo esc_attr($order->get_order_key()); ?>" />

            <label for="order_email" class="axiom-order-verify-label">Email address</label>
            <input
                type="email"
                class="axiom-order-verify-input"
                name="order_email"
                id="order_email"
                autocomplete="email"
                required
            />

            <button type="submit" class="axiom-order-verify-button">
                Verify Order
            </button>
        </form>

        <div class="axiom-order-verify-help">
            Need help? Contact us at
            <a href="mailto:realaxiompeptides@gmail.com">realaxiompeptides@gmail.com</a>
        </div>
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
  font-size:42px;
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
.axiom-order-verify-label{
  color:#0f172a;
  font-size:15px;
  font-weight:800;
}
.axiom-order-verify-input{
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
.axiom-order-verify-input:focus{
  outline:none;
  border-color:#5aa8df;
  background:#ffffff;
}
.axiom-order-verify-button{
  min-height:56px;
  border:0;
  border-radius:999px;
  background:linear-gradient(135deg,#4aa7e8,#2f84bf);
  color:#ffffff;
  font-size:18px;
  font-weight:900;
  cursor:pointer;
}
.axiom-order-verify-help{
  margin-top:16px;
  color:#64748b;
  font-size:15px;
  line-height:1.6;
}
.axiom-order-verify-help a{
  color:#2f84bf;
  font-weight:800;
  text-decoration:none;
}
@media (max-width: 767px){
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
  .axiom-order-verify-input{
    font-size:16px;
    min-height:54px;
  }
  .axiom-order-verify-button{
    min-height:54px;
    font-size:17px;
  }
}
</style>
