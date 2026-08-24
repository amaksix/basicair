(function () {
  var pageMap = {
    index: "",
    shop: "shop/",
    services: "services/",
    service: "services/",
    contact: "contact/",
    product: "products/"
  };

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
        window.location.href = "/" + locale + "/products/" + productSlug + "/";
        return;
      }

      if (currentPage === "service" && serviceSlug) {
        window.location.href = "/" + locale + "/services/" + serviceSlug + "/";
        return;
      }

      window.location.href = "/" + locale + "/" + path;
    });
  }

  document.querySelectorAll(".type-languages").forEach(bindLanguageSelect);
})();
