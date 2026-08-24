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
  return JSON.parse(fs.readFileSync(filePath, "utf8"));
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
      const data = JSON.parse(fs.readFileSync(path.join(productsDir, file), "utf8"));
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
      const data = JSON.parse(fs.readFileSync(path.join(servicesDir, file), "utf8"));
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
};
