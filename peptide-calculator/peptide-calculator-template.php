<<?php
/*
Template Name: Peptide Calculator Template
*/
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$products = array(
    array(
        'name' => 'BPC-157',
        'variants' => array(
            array('label' => '5mg', 'mg' => 5),
        ),
    ),
    array(
        'name' => 'GHK-CU',
        'variants' => array(
            array('label' => '50mg / 3mL', 'mg' => 50),
            array('label' => '100mg / 3mL', 'mg' => 100),
        ),
    ),
    array(
        'name' => 'GLP-3 RT',
        'variants' => array(
            array('label' => '10mg / 3mL', 'mg' => 10),
            array('label' => '20mg / 3mL', 'mg' => 20),
        ),
    ),
    array(
        'name' => 'CJC with DAC',
        'variants' => array(
            array('label' => '5mg', 'mg' => 5),
            array('label' => '10mg', 'mg' => 10),
        ),
    ),
    array(
        'name' => 'Cerebrolysin',
        'variants' => array(
            array('label' => '60mg', 'mg' => 60),
        ),
    ),
    array(
        'name' => 'Sermorelin',
        'variants' => array(
            array('label' => '5mg', 'mg' => 5),
        ),
    ),
    array(
        'name' => 'Tesamorelin',
        'variants' => array(
            array('label' => '5mg', 'mg' => 5),
        ),
    ),
    array(
        'name' => 'Semax',
        'variants' => array(
            array('label' => '10mg / 3mL', 'mg' => 10),
        ),
    ),
    array(
        'name' => 'DSIP',
        'variants' => array(
            array('label' => '5mg', 'mg' => 5),
        ),
    ),
    array(
        'name' => 'NAD+',
        'variants' => array(
            array('label' => '500mg', 'mg' => 500),
            array('label' => '1000mg', 'mg' => 1000),
        ),
    ),
    array(
        'name' => 'KPV',
        'variants' => array(
            array('label' => '5mg', 'mg' => 5),
        ),
    ),
    array(
        'name' => 'PT-141',
        'variants' => array(
            array('label' => '10mg', 'mg' => 10),
        ),
    ),
    array(
        'name' => 'Pinealon',
        'variants' => array(
            array('label' => '10mg', 'mg' => 10),
        ),
    ),
    array(
        'name' => 'Selank',
        'variants' => array(
            array('label' => '10mg', 'mg' => 10),
        ),
    ),
    array(
        'name' => 'Glow',
        'variants' => array(
            array('label' => '10mg', 'mg' => 10),
        ),
    ),
    array(
        'name' => 'Kisspeptin',
        'variants' => array(
            array('label' => '10mg', 'mg' => 10),
        ),
    ),
    array(
        'name' => 'ARA-290',
        'variants' => array(
            array('label' => '10mg', 'mg' => 10),
        ),
    ),
    array(
        'name' => 'SS-31',
        'variants' => array(
            array('label' => '10mg', 'mg' => 10),
        ),
    ),
    array(
        'name' => 'MT-1',
        'variants' => array(
            array('label' => '10mg', 'mg' => 10),
        ),
    ),
    array(
        'name' => 'Glutathione',
        'variants' => array(
            array('label' => '1500mg', 'mg' => 1500),
        ),
    ),
);
?>

<main class="axiom-calculator-page">
  <div class="container">
    <section class="axiom-calculator-hero">
      <p class="axiom-calculator-kicker">Research Calculator</p>
      <h1>Peptide Calculator</h1>
      <p class="axiom-calculator-subtitle">
        Choose your syringe size, vial size, product, and variant to calculate concentration and conversion math for research preparation.
      </p>
    </section>

    <section class="axiom-calculator-shell">
      <div class="axiom-calculator-card">
        <div class="axiom-calculator-step">
          <h2>Syringe Size</h2>
          <div class="axiom-calculator-choice-grid axiom-calculator-choice-grid-needle">
            <button type="button" class="axiom-choice-btn is-active" data-units="30">30 Units</button>
            <button type="button" class="axiom-choice-btn" data-units="50">50 Units</button>
            <button type="button" class="axiom-choice-btn" data-units="100">100 Units</button>
          </div>
        </div>

        <div class="axiom-calculator-step">
          <h2>Vial Size</h2>
          <div class="axiom-calculator-choice-grid axiom-calculator-choice-grid-vial">
            <button type="button" class="axiom-choice-btn is-active" data-vial-ml="3">3 mL</button>
            <button type="button" class="axiom-choice-btn" data-vial-ml="10">10 mL</button>
          </div>
        </div>

        <div class="axiom-calculator-grid">
          <div class="axiom-calculator-field">
            <label for="axiomCalcProduct">Product</label>
            <select id="axiomCalcProduct"></select>
          </div>

          <div class="axiom-calculator-field">
            <label for="axiomCalcVariant">Variant</label>
            <select id="axiomCalcVariant"></select>
          </div>

          <div class="axiom-calculator-field">
            <label for="axiomCalcVialMg">Vial Content (mg)</label>
            <input type="number" id="axiomCalcVialMg" min="0" step="0.01">
          </div>

          <div class="axiom-calculator-field">
            <label for="axiomCalcReconMl">Reconstitution Amount (mL)</label>
            <input type="number" id="axiomCalcReconMl" min="0.01" step="0.01" value="3">
          </div>

          <div class="axiom-calculator-field">
            <label for="axiomCalcTargetMg">Target Amount (mg)</label>
            <input type="number" id="axiomCalcTargetMg" min="0" step="0.001" placeholder="Example: 0.25">
          </div>

          <div class="axiom-calculator-field">
            <label for="axiomCalcSyringeUnits">Needle Units</label>
            <input type="number" id="axiomCalcSyringeUnits" value="30" readonly>
          </div>
        </div>

        <div class="axiom-calculator-actions">
          <button type="button" id="axiomCalcRun" class="axiom-calculator-btn">Calculate</button>
          <button type="button" id="axiomCalcReset" class="axiom-calculator-btn axiom-calculator-btn-secondary">Reset</button>
        </div>
      </div>

      <div class="axiom-calculator-results">
        <div class="axiom-calculator-result-card">
          <span>Concentration</span>
          <strong id="axiomResultMgPerMl">—</strong>
          <p>mg per mL after reconstitution</p>
        </div>

        <div class="axiom-calculator-result-card">
          <span>Per 10 units</span>
          <strong id="axiomResultPer10Units">—</strong>
          <p>mg in 10 units</p>
        </div>

        <div class="axiom-calculator-result-card">
          <span>Target amount</span>
          <strong id="axiomResultUnitsNeeded">—</strong>
          <p>units needed for target mg</p>
        </div>

        <div class="axiom-calculator-result-card">
          <span>Total doses per vial</span>
          <strong id="axiomResultTotalDoses">—</strong>
          <p>approximate target doses per vial</p>
        </div>
      </div>

      <div class="axiom-calculator-table-card">
        <div class="axiom-calculator-table-header">
          <h2>Quick Unit Table</h2>
          <p>Reference table based on your current settings.</p>
        </div>

        <div class="axiom-calculator-table-wrap">
          <table class="axiom-calculator-table">
            <thead>
              <tr>
                <th>Units</th>
                <th>mL</th>
                <th>mg</th>
              </tr>
            </thead>
            <tbody id="axiomCalcTableBody"></tbody>
          </table>
        </div>
      </div>

      <div class="axiom-calculator-disclaimer">
        <strong>Research use only.</strong>
        <p>
          This calculator is for concentration math and preparation conversions only. It does not provide medical advice or usage recommendations.
        </p>
      </div>
    </section>
  </div>
</main>

<script>
window.AXIOM_CALCULATOR_PRODUCTS = <?php echo wp_json_encode($products); ?>;
</script>

<?php get_footer(); ?>
