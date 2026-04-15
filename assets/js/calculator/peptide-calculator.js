document.addEventListener("DOMContentLoaded", function () {
  const products = window.AXIOM_CALCULATOR_PRODUCTS || [];

  const productSelect = document.getElementById("axiomCalcProduct");
  const variantSelect = document.getElementById("axiomCalcVariant");
  const vialMgInput = document.getElementById("axiomCalcVialMg");
  const reconMlInput = document.getElementById("axiomCalcReconMl");
  const targetMgInput = document.getElementById("axiomCalcTargetMg");
  const syringeUnitsSelect = document.getElementById("axiomCalcSyringeUnits");
  const runBtn = document.getElementById("axiomCalcRun");
  const resetBtn = document.getElementById("axiomCalcReset");

  const resultMgPerMl = document.getElementById("axiomResultMgPerMl");
  const resultPer10Units = document.getElementById("axiomResultPer10Units");
  const resultUnitsNeeded = document.getElementById("axiomResultUnitsNeeded");
  const resultTotalDoses = document.getElementById("axiomResultTotalDoses");
  const tableBody = document.getElementById("axiomCalcTableBody");

  function formatNumber(value, decimals = 3) {
    if (!isFinite(value)) return "—";
    return Number(value).toFixed(decimals).replace(/\.?0+$/, "");
  }

  function setVariantOptions(productIndex) {
    const product = products[productIndex];
    variantSelect.innerHTML = "";

    if (!product || !product.variants || !product.variants.length) return;

    product.variants.forEach((variant, index) => {
      const option = document.createElement("option");
      option.value = index;
      option.textContent = variant.label;
      variantSelect.appendChild(option);
    });

    applyVariantValues();
  }

  function applyVariantValues() {
    const product = products[productSelect.value];
    if (!product) return;

    const variant = product.variants[variantSelect.value];
    if (!variant) return;

    vialMgInput.value = variant.mg || "";
    if (variant.ml && !reconMlInput.value) {
      reconMlInput.value = variant.ml;
    }
  }

  function buildQuickTable(mgPerMl, unitsPerMl) {
    tableBody.innerHTML = "";
    const steps = [5, 10, 15, 20, 25, 30, 40, 50, 75, 100];

    steps.forEach((units) => {
      const ml = units / unitsPerMl;
      const mg = ml * mgPerMl;

      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${units}</td>
        <td>${formatNumber(ml, 3)} mL</td>
        <td>${formatNumber(mg, 3)} mg</td>
      `;
      tableBody.appendChild(row);
    });
  }

  function calculate() {
    const vialMg = parseFloat(vialMgInput.value || "0");
    const reconMl = parseFloat(reconMlInput.value || "0");
    const targetMg = parseFloat(targetMgInput.value || "0");
    const unitsPerMl = parseFloat(syringeUnitsSelect.value || "100");

    if (!vialMg || !reconMl || !unitsPerMl) {
      resultMgPerMl.textContent = "—";
      resultPer10Units.textContent = "—";
      resultUnitsNeeded.textContent = "—";
      resultTotalDoses.textContent = "—";
      tableBody.innerHTML = "";
      return;
    }

    const mgPerMl = vialMg / reconMl;
    const mgPerUnit = mgPerMl / unitsPerMl;
    const mgPer10Units = mgPerUnit * 10;
    const unitsNeeded = targetMg > 0 ? targetMg / mgPerUnit : 0;
    const totalDoses = targetMg > 0 ? vialMg / targetMg : 0;

    resultMgPerMl.textContent = `${formatNumber(mgPerMl, 3)} mg/mL`;
    resultPer10Units.textContent = `${formatNumber(mgPer10Units, 3)} mg`;
    resultUnitsNeeded.textContent = targetMg > 0 ? `${formatNumber(unitsNeeded, 1)} units` : "—";
    resultTotalDoses.textContent = targetMg > 0 ? `${formatNumber(totalDoses, 1)} doses` : "—";

    buildQuickTable(mgPerMl, unitsPerMl);
  }

  function resetCalculator() {
    productSelect.selectedIndex = 0;
    setVariantOptions(0);
    reconMlInput.value = "";
    targetMgInput.value = "";
    syringeUnitsSelect.value = "100";
    calculate();
  }

  if (productSelect && variantSelect) {
    products.forEach((product, index) => {
      const option = document.createElement("option");
      option.value = index;
      option.textContent = product.name;
      productSelect.appendChild(option);
    });

    productSelect.addEventListener("change", function () {
      setVariantOptions(productSelect.value);
      calculate();
    });

    variantSelect.addEventListener("change", function () {
      applyVariantValues();
      calculate();
    });

    [vialMgInput, reconMlInput, targetMgInput, syringeUnitsSelect].forEach((el) => {
      if (el) {
        el.addEventListener("input", calculate);
        el.addEventListener("change", calculate);
      }
    });

    if (runBtn) {
      runBtn.addEventListener("click", calculate);
    }

    if (resetBtn) {
      resetBtn.addEventListener("click", resetCalculator);
    }

    setVariantOptions(0);
    calculate();
  }
});
