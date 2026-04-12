<div id="site-footer"></div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const mount = document.getElementById("ageGateMount");
  const themeBase = "<?php echo esc_js(get_template_directory_uri()); ?>";

  if (mount) {
    fetch(themeBase + "/parts/age-gate.html", { cache: "no-store" })
      .then(function (response) {
        if (!response.ok) {
          throw new Error("Could not load age gate HTML");
        }
        return response.text();
      })
      .then(function (html) {
        mount.innerHTML = html;

        if (window.AXIOM_AGE_GATE && typeof window.AXIOM_AGE_GATE.init === "function") {
          window.AXIOM_AGE_GATE.init({
            logoPath: themeBase + "/assets/images/axiom-menu-logo.PNG",
            exitUrl: "https://www.google.com"
          });
        }
      })
      .catch(function (error) {
        console.error("Age gate failed to load:", error);
      });
  }

  setTimeout(function () {
    try {
      const rawCart = localStorage.getItem("axiom_cart");
      const cart = JSON.parse(rawCart || "[]");
      const products = Array.isArray(window.AXIOM_PRODUCTS)
        ? window.AXIOM_PRODUCTS
        : Array.isArray(window.productData)
          ? window.productData
          : [];

      if (!Array.isArray(cart) || !cart.length || !products.length) return;

      let needsUpdate = false;

      function normalizeImagePath(path) {
        if (!path || typeof path !== "string") return "";
        const cleanPath = path.trim();

        if (
          cleanPath.startsWith("http://") ||
          cleanPath.startsWith("https://") ||
          cleanPath.startsWith("/")
        ) {
          return cleanPath;
        }

        if (cleanPath.startsWith("./")) {
          return cleanPath.replace("./", "");
        }

        if (cleanPath.startsWith("../")) {
          return cleanPath.replace("../", "");
        }

        return cleanPath;
      }

      function getProductImage(product) {
        if (!product) return "";

        if (typeof product.image === "string" && product.image.trim()) {
          return normalizeImagePath(product.image);
        }

        if (Array.isArray(product.images) && product.images.length) {
          const firstImage = product.images[0];
          if (typeof firstImage === "string" && firstImage.trim()) {
            return normalizeImagePath(firstImage);
          }
        }

        return "";
      }

      const fixedCart = cart.map(function (item) {
        if (item.image && String(item.image).trim()) {
          return item;
        }

        const match = products.find(function (product) {
          if (item.slug && product.slug && item.slug === product.slug) return true;
          if (item.id && product.id && String(item.id) === String(product.id)) return true;

          if (Array.isArray(product.variants)) {
            return product.variants.some(function (variant) {
              return item.id && variant.id && String(item.id) === String(variant.id);
            });
          }

          return false;
        });

        const image = getProductImage(match);
        if (!image) return item;

        needsUpdate = true;
        return {
          ...item,
          image: image
        };
      });

      if (needsUpdate) {
        localStorage.setItem("axiom_cart", JSON.stringify(fixedCart));
        window.dispatchEvent(new Event("axiom-cart-updated"));
        window.dispatchEvent(new Event("storage"));
      }
    } catch (error) {
      console.error("Failed to repair cart images:", error);
    }
  }, 500);
});
</script>

<?php wp_footer(); ?>
</body>
</html>
