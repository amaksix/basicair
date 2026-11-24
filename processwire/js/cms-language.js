(function () {
  var pageMap = {
    index: "",
    shop: "shop/",
    "product-category": "shop/",
    services: "services/",
    service: "services/",
    industries: "industries/",
    "industry-market": "industries/",
    article: "zinojumi/",
    news: "zinojumi/",
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
      var categorySlug = select.dataset.categorySlug;
      var articleSlug = select.dataset.articleSlug;
      var marketSlug = select.dataset.marketSlug;
      var path = pageMap[currentPage] || "";

      if (currentPage === "product" && productSlug) {
        window.location.href = withPrefix("/" + locale + "/products/" + productSlug + "/");
        return;
      }

      if (currentPage === "service" && serviceSlug) {
        window.location.href = withPrefix("/" + locale + "/services/" + serviceSlug + "/");
        return;
      }

      if (currentPage === "product-category" && categorySlug) {
        window.location.href = withPrefix("/" + locale + "/shop/" + categorySlug + "/");
        return;
      }

      if (currentPage === "article" && articleSlug) {
        window.location.href = withPrefix("/" + locale + "/zinojumi/" + articleSlug + "/");
        return;
      }

      if (currentPage === "industry-market" && marketSlug) {
        window.location.href = withPrefix("/" + locale + "/industries/" + marketSlug + "/");
        return;
      }

      window.location.href = withPrefix("/" + locale + "/" + path);
    });
  }

  document.querySelectorAll(".type-languages").forEach(bindLanguageSelect);
})();
