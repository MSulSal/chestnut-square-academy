import path from "node:path";
import playwright from "../.npm-cache/_npx/e41f203b7505f1fb/node_modules/playwright/index.js";

const { chromium } = playwright;

const base = "http://chestnutsquareacademy.local";
const routes = [
  "/",
  "/academies/",
  "/academies/approach-to-childcare/",
  "/our-curriculum/",
  "/company/",
  "/contact-us/",
  "/faq/",
  "/academies/enrollment-and-tuition/",
  "/for-parents/",
  "/franchising/",
  "/academies/programs/infant-daycare/",
  "/academies/programs/school-age-programs/"
];

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage({ viewport: { width: 1280, height: 720 } });

for (const route of routes) {
  const url = `${base}${route}`;
  await page.goto(url, { waitUntil: "domcontentloaded", timeout: 60000 });
  await page.waitForTimeout(1400);
  const slug = route === "/"
    ? "home"
    : route.replace(/^\//, "").replace(/\/$/, "").replace(/[\/]/g, "-");

  const output = path.join("docs", `local-${slug}-top-1280-pass7.png`);
  await page.screenshot({ path: output, fullPage: false });
  console.log(`saved ${output}`);
}

await browser.close();
