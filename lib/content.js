const fs = require("fs");
const path = require("path");

const LOCALES = ["lv", "en", "ru"];
const DEFAULT_LOCALE = "lv";
const CONTENT_DIR = path.join(__dirname, "..", "content");

function readJson(relativePath) {
  const filePath = path.join(CONTENT_DIR, relativePath);
  if (!fs.existsSync(filePath)) {
    throw new Error(`Missing content file: ${relativePath}`);
  }
  const raw = fs.readFileSync(filePath, "utf8").replace(/^\uFEFF/, "");
  return JSON.parse(raw);
}

function loadLocaleContent(locale, name) {
  return readJson(path.join(locale, `${name}.json`));
}

function loadProducts(locale) {
  const productsDir = path.join(CONTENT_DIR, locale, "products");
  if (!fs.existsSync(productsDir)) return [];

  return fs
    .readdirSync(productsDir)
    .filter((file) => file.endsWith(".json"))
    .map((file) => {
      const slug = file.replace(/\.json$/, "");
      const data = JSON.parse(fs.readFileSync(path.join(productsDir, file), "utf8").replace(/^\uFEFF/, ""));
      return { ...data, slug: data.slug || slug };
    })
    .sort((a, b) => (a.order || 0) - (b.order || 0));
}

function loadServices(locale) {
  const servicesDir = path.join(CONTENT_DIR, locale, "services");
  if (!fs.existsSync(servicesDir)) return [];

  return fs
    .readdirSync(servicesDir)
    .filter((file) => file.endsWith(".json"))
    .map((file) => {
      const slug = file.replace(/\.json$/, "");
      const data = JSON.parse(fs.readFileSync(path.join(servicesDir, file), "utf8").replace(/^\uFEFF/, ""));
      return { ...data, slug: data.slug || slug };
    })
    .sort((a, b) => (a.order || 0) - (b.order || 0));
}

function loadProductCategories(locale) {
  const categoriesDir = path.join(CONTENT_DIR, locale, "product-categories");
  if (!fs.existsSync(categoriesDir)) return [];

  return fs
    .readdirSync(categoriesDir)
    .filter((file) => file.endsWith(".json"))
    .map((file) => {
      const slug = file.replace(/\.json$/, "");
      const data = JSON.parse(fs.readFileSync(path.join(categoriesDir, file), "utf8").replace(/^\uFEFF/, ""));
      return { ...data, slug: data.slug || slug };
    })
    .sort((a, b) => (a.order || 0) - (b.order || 0));
}

module.exports = {
  LOCALES,
  DEFAULT_LOCALE,
  loadLocaleContent,
  loadProducts,
  loadServices,
  loadProductCategories,
};
