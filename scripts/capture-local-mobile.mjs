import path from "node:path";
import playwright from "../.npm-cache/_npx/e41f203b7505f1fb/node_modules/playwright/index.js";

const { chromium } = playwright;
const base = "http://chestnutsquareacademy.local";
const routes = ["/", "/academies/", "/our-curriculum/", "/company/", "/contact-us/"];

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage({ viewport: { width: 390, height: 844 } });

for (const route of routes) {
  await page.goto(`${base}${route}`, { waitUntil: "domcontentloaded", timeout: 60000 });
  await page.waitForTimeout(1400);
  const slug = route === "/" ? "home" : route.replace(/^\//, "").replace(/\/$/, "").replace(/[\/]/g, "-");
  const output = path.join("docs", `local-${slug}-mobile-pass7.png`);
  await page.screenshot({ path: output, fullPage: false });
  console.log(`saved ${output}`);
}

await browser.close();
