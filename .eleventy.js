const { loadLocaleContent, loadProducts, loadServices, LOCALES, DEFAULT_LOCALE } = require("./lib/content");

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
    products: (data) => (data.locale ? loadProducts(data.locale) : []),
    serviceItems: (data) => (data.locale ? loadServices(data.locale) : []),
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

  eleventyConfig.addFilter("price", (value) => {
    if (value === null || value === undefined || value === "") return "";
    const num = Number(value);
    if (Number.isNaN(num)) return value;
    return `€${num.toFixed(2)}`;
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
