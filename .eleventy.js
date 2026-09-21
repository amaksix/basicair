const {
  loadLocaleContent,
  loadProducts,
  loadServices,
  loadProductCategories,
  loadIndustryMarkets,
  loadArticles,
  LOCALES,
  DEFAULT_LOCALE,
} = require("./lib/content");

module.exports = async function (eleventyConfig) {
  // Eleventy v3 is ESM-only; load plugins via dynamic import in CommonJS config
  const { EleventyHtmlBasePlugin } = await import("@11ty/eleventy");

  // Rewrites absolute /href and /src for GitHub Pages project URLs (--pathprefix)
  eleventyConfig.addPlugin(EleventyHtmlBasePlugin);

  eleventyConfig.addPassthroughCopy("css");
  eleventyConfig.addPassthroughCopy("js");
  eleventyConfig.addPassthroughCopy("images");
  eleventyConfig.addPassthroughCopy("fonts");
  eleventyConfig.addPassthroughCopy("admin");
  eleventyConfig.addPassthroughCopy("mail");
  eleventyConfig.addPassthroughCopy("contact");
  eleventyConfig.addPassthroughCopy("examples");

  // GitHub Pages: skip Jekyll processing of the artifact
  eleventyConfig.on("eleventy.after", async ({ dir }) => {
    const fs = require("fs");
    const path = require("path");
    fs.writeFileSync(path.join(dir.output, ".nojekyll"), "");
  });

  // Rebuild when CMS/content JSON changes (outside src/)
  eleventyConfig.addWatchTarget("content");
  eleventyConfig.addWatchTarget("scss");

  eleventyConfig.addGlobalData("locales", LOCALES);
  eleventyConfig.addGlobalData("defaultLocale", DEFAULT_LOCALE);
  eleventyConfig.addGlobalData("year", () => new Date().getFullYear());

  eleventyConfig.addGlobalData("eleventyComputed", {
    settings: (data) => (data.locale ? loadLocaleContent(data.locale, "settings") : null),
    home: (data) => (data.locale ? loadLocaleContent(data.locale, "pages/home") : null),
    services: (data) => (data.locale ? loadLocaleContent(data.locale, "pages/services") : null),
    contact: (data) => (data.locale ? loadLocaleContent(data.locale, "pages/contact") : null),
    shop: (data) => (data.locale ? loadLocaleContent(data.locale, "pages/shop") : null),
    industries: (data) => (data.locale ? loadLocaleContent(data.locale, "pages/industries") : null),
    articles: (data) => (data.locale ? loadLocaleContent(data.locale, "pages/articles") : null),
    news: (data) => (data.locale ? loadLocaleContent(data.locale, "pages/news") : null),
    products: (data) => (data.locale ? loadProducts(data.locale) : []),
    serviceItems: (data) => (data.locale ? loadServices(data.locale) : []),
    productCategories: (data) => (data.locale ? loadProductCategories(data.locale) : []),
    industryMarkets: (data) => (data.locale ? loadIndustryMarkets(data.locale) : []),
    articleItems: (data) => (data.locale ? loadArticles(data.locale) : []),
  });

  eleventyConfig.addCollection("allProducts", () => {
    const items = [];
    for (const locale of LOCALES) {
      for (const product of loadProducts(locale)) {
        items.push({ ...product, locale });
      }
    }
    return items;
  });

  eleventyConfig.addCollection("allServices", () => {
    const items = [];
    for (const locale of LOCALES) {
      for (const service of loadServices(locale)) {
        items.push({ ...service, locale });
      }
    }
    return items;
  });

  eleventyConfig.addCollection("allProductCategories", () => {
    const items = [];
    for (const locale of LOCALES) {
      for (const category of loadProductCategories(locale)) {
        items.push({ ...category, locale });
      }
    }
    return items;
  });

  eleventyConfig.addCollection("allIndustryMarkets", () => {
    const items = [];
    for (const locale of LOCALES) {
      for (const market of loadIndustryMarkets(locale)) {
        items.push({ ...market, locale });
      }
    }
    return items;
  });

  eleventyConfig.addCollection("allArticles", () => {
    const items = [];
    for (const locale of LOCALES) {
      for (const article of loadArticles(locale)) {
        items.push({ ...article, locale });
      }
    }
    return items;
  });

  eleventyConfig.addFilter("formatDate", (value, locale = "lv") => {
    if (!value) return "";
    const date = value instanceof Date ? value : new Date(value);
    if (Number.isNaN(date.getTime())) return String(value);
    const localeMap = { lv: "lv-LV", en: "en-GB", ru: "ru-RU" };
    return date.toLocaleDateString(localeMap[locale] || "lv-LV", {
      year: "numeric",
      month: "long",
      day: "numeric",
    });
  });

  eleventyConfig.addFilter("localePath", (pagePath, locale) => {
    const normalized = pagePath.replace(/^\//, "").replace(/index\.html$/, "");
    const suffix = normalized ? `${normalized}/` : "";
    return `/${locale}/${suffix}`;
  });

  eleventyConfig.addFilter("asset", (assetPath) => {
    if (!assetPath) return "";
    if (assetPath.startsWith("http")) return assetPath;
    return assetPath.startsWith("/") ? assetPath : `/${assetPath}`;
  });

  eleventyConfig.addFilter("nl2br", (value) => {
    if (!value) return "";
    return String(value).replace(/\n/g, "<br>");
  });

  eleventyConfig.addShortcode("year", () => String(new Date().getFullYear()));

  return {
    dir: {
      input: "src",
      output: "_site",
      includes: "_includes",
      data: "_data",
    },
    htmlTemplateEngine: "njk",
    markdownTemplateEngine: "njk",
    templateFormats: ["njk", "html", "md"],
  };
};
