<?php
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
            array('label' => '5mg', 'mg' => 5, 'ml' => 3),
        ),
    ),
    array(
        'name' => 'BAC Water',
        'variants' => array(
            array('label' => '10mL', 'mg' => 0, 'ml' => 10),
        ),
    ),
    array(
        'name' => 'GHK-CU',
        'variants' => array(
            array('label' => '50mg / 3mL', 'mg' => 50, 'ml' => 3),
            array('label' => '100mg / 3mL', 'mg' => 100, 'ml' => 3),
        ),
    ),
    array(
        'name' => 'GLP-3 RT',
        'variants' => array(
            array('label' => '10mg / 3mL', 'mg' => 10, 'ml' => 3),
            array('label' => '20mg / 3mL', 'mg' => 20, 'ml' => 3),
        ),
    ),
    array(
        'name' => 'CJC with DAC',
        'variants' => array(
            array('label' => '5mg', 'mg' => 5, 'ml' => 0),
            array('label' => '10mg', 'mg' => 10, 'ml' => 0),
        ),
    ),
    array(
        'name' => 'Cerebrolysin',
        'variants' => array(
            array('label' => '60mg', 'mg' => 60, 'ml' => 0),
        ),
    ),
    array(
        'name' => 'Sermorelin',
        'variants' => array(
            array('label' => '5mg', 'mg' => 5, 'ml' => 0),
        ),
    ),
    array(
        'name' => 'Tesamorelin',
        'variants' => array(
            array('label' => '5mg', 'mg' => 5, 'ml' => 0),
        ),
    ),
    array(
        'name' => 'Semax',
        'variants' => array(
            array('label' => '10mg / 3mL', 'mg' => 10, 'ml' => 3),
        ),
    ),
    array(
        'name' => 'DSIP',
        'variants' => array(
            array('label' => '5mg', 'mg' => 5, 'ml' => 0),
        ),
    ),
    array(
        'name' => 'NAD+',
        'variants' => array(
            array('label' => '500mg', 'mg' => 500, 'ml' => 0),
            array('label' => '1000mg', 'mg' => 1000, 'ml' => 0),
        ),
    ),
    array(
        'name' => 'KPV',
        'variants' => array(
            array('label' => '5mg', 'mg' => 5, 'ml' => 0),
        ),
    ),
    array(
        'name' => 'PT-141',
        'variants' => array(
            array('label' => '10mg', 'mg' => 10, 'ml' => 0),
        ),
    ),
    array(
        'name' => 'Pinealon',
        'variants' => array(
            array('label' => '10mg', 'mg' => 10, 'ml' => 0),
        ),
    ),
    array(
        'name' => 'Selank',
        'variants' => array(
            array('label' => '10mg', 'mg' => 10, 'ml' => 0),
        ),
    ),
    array(
        'name' => 'Glow',
        'variants' => array(
            array('label' => '10mg', 'mg' => 10, 'ml' => 0),
        ),
    ),
    array(
        'name' => 'Kisspeptin',
        'variants' => array(
            array('label' => '10mg', 'mg' => 10, 'ml' => 0),
        ),
    ),
    array(
        'name' => 'ARA-290',
        'variants' => array(
            array('label' => '10mg', 'mg' => 10, 'ml' => 0),
        ),
    ),
    array(
        'name' => 'SS-31',
        'variants' => array(
            array('label' => '10mg', 'mg' => 10, 'ml' => 0),
        ),
    ),
    array(
        'name' => 'MT-1',
        'variants' => array(
            array('label' => '10mg', 'mg' => 10, 'ml' => 0),
        ),
    ),
    array(
        'name' => 'Glutathione',
        'variants' => array(
            array('label' => '1500mg', 'mg' => 1500, 'ml' => 0),
        ),
    ),
);

?>

<main class="axiom-calculator-page">
  <div class="container">
    <section class="axiom-calculator-hero">
      <p class="axiom-calculator-kicker">Research Calculator</p>
      <h1>Peptide Reconstitution Calculator</h1>
      <p class="axiom-calculator-subtitle">
        Select a product and variant, enter your reconstitution amount, and calculate concentration, insulin units, and dose conversions for research preparation.
      </p>
    </section>

    <section class="axiom-calculator-shell">
      <div class="axiom-calculator-card">
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
            <input type="number" id="axiomCalcReconMl" min="0.01" step="0.01" placeholder="Example: 2">
          </div>

          <div class="axiom-calculator-field">
            <label for="axiomCalcTargetMg">Target Amount (mg)</label>
            <input type="number" id="axiomCalcTargetMg" min="0" step="0.001" placeholder="Example: 0.25">
          </div>

          <div class="axiom-calculator-field">
            <label for="axiomCalcSyringeUnits">Insulin Syringe Size (units per 1mL)</label>
            <select id="axiomCalcSyringeUnits">
              <option value="100">100 units / 1mL</option>
              <option value="50">50 units / 1mL</option>
              <option value="30">30 units / 0.3mL</option>
            </select>
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
          <span>Per 10 insulin units</span>
          <strong id="axiomResultPer10Units">—</strong>
          <p>mg in 10 units</p>
        </div>

        <div class="axiom-calculator-result-card">
          <span>Target amount</span>
          <strong id="axiomResultUnitsNeeded">—</strong>
          <p>insulin units needed for target mg</p>
        </div>

        <div class="axiom-calculator-result-card">
          <span>Total doses per vial</span>
          <strong id="axiomResultTotalDoses">—</strong>
          <p>approximate number of target doses</p>
        </div>
      </div>

      <div class="axiom-calculator-table-card">
        <div class="axiom-calculator-table-header">
          <h2>Quick Unit Table</h2>
          <p>Reference table based on the current reconstitution settings.</p>
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
          This calculator is provided for preparation math and concentration conversions only. It does not provide medical advice, diagnosis, treatment guidance, or usage recommendations.
        </p>
      </div>
    </section>
  </div>
</main>

<script>
window.AXIOM_CALCULATOR_PRODUCTS = <?php echo wp_json_encode($products); ?>;
</script>

<?php get_footer(); ?>
