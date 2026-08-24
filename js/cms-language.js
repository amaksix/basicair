(function () {
  var pageMap = {
    index: "",
    shop: "shop/",
    services: "services/",
    service: "services/",
    contact: "contact/",
    product: "products/"
  };

  function withPrefix(path) {
    var base = (window.__PATH_PREFIX__ || "/").replace(/\/$/, "");
    if (!path.startsWith("/")) path = "/" + path;
    return base + path;
  }

  function bindLanguageSelect(select) {
    if (!select || select.dataset.bound === "true") return;
    select.dataset.bound = "true";

    select.addEventListener("change", function () {
      var locale = select.value;
      var currentPage = select.dataset.currentPage || "index";
      var productSlug = select.dataset.productSlug;
      var serviceSlug = select.dataset.serviceSlug;
      var path = pageMap[currentPage] || "";

      if (currentPage === "product" && productSlug) {
        window.location.href = withPrefix("/" + locale + "/products/" + productSlug + "/");
        return;
      }

      if (currentPage === "service" && serviceSlug) {
        window.location.href = withPrefix("/" + locale + "/services/" + serviceSlug + "/");
        return;
      }

      window.location.href = withPrefix("/" + locale + "/" + path);
    });
  }

  document.querySelectorAll(".type-languages").forEach(bindLanguageSelect);
})();
