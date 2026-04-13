<?php
defined('ABSPATH') || exit;
?>

<div class="axiom-checkout-research-wrap axiom-checkout-research-wrap--payment">
  <div class="axiom-research-use-box">
    <div class="axiom-research-use-icon">
      <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
    </div>

    <div class="axiom-research-use-copy">
      <strong>I acknowledge this order is for research use only</strong>
      <p>
        All products are intended strictly for laboratory, analytical, and in-vitro
        research use only. Not for human or veterinary consumption.
      </p>
    </div>
  </div>

  <?php
  woocommerce_form_field(
    'axiom_research_use_ack',
    array(
      'type'     => 'checkbox',
      'class'    => array('form-row-wide', 'axiom-checkout-checkbox-row'),
      'required' => true,
      'label'    => 'I understand and agree',
    ),
    WC()->checkout()->get_value('axiom_research_use_ack')
  );
  ?>
</div>
