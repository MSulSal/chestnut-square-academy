import fs from "node:fs";
import path from "node:path";
import playwright from "../.npm-cache/_npx/e41f203b7505f1fb/node_modules/playwright/index.js";

const { chromium } = playwright;

const base = "http://chestnutsquareacademy.local";
const profiles = [
  { key: "desktop", viewport: { width: 1280, height: 720 } },
  { key: "mobile", viewport: { width: 390, height: 844 } },
];

function nowStamp() {
  const d = new Date();
  const pad = (n) => String(n).padStart(2, "0");
  return `${d.getFullYear()}${pad(d.getMonth() + 1)}${pad(d.getDate())}-${pad(
    d.getHours()
  )}${pad(d.getMinutes())}${pad(d.getSeconds())}`;
}

function slugFromRoute(route) {
  if (route === "/") {
    return "home";
  }
  return route.replace(/^\//, "").replace(/\/$/, "").replace(/[\/]/g, "-");
}

function buildScrollPositions(totalHeight, viewportHeight) {
  const maxScroll = Math.max(0, totalHeight - viewportHeight);
  const step = Math.max(120, viewportHeight - 120);
  const positions = [];

  for (let y = 0; y <= maxScroll; y += step) {
    positions.push(y);
  }

  if (!positions.includes(maxScroll)) {
    positions.push(maxScroll);
  }

  return positions;
}

async function getPublishedRoutes() {
  const endpoint = `${base}/wp-json/wp/v2/pages?per_page=100&_fields=link,status,slug`;
  const res = await fetch(endpoint);
  if (!res.ok) {
    throw new Error(`Failed to fetch pages from ${endpoint}: ${res.status}`);
  }

  const pages = await res.json();
  const routes = [];
  const seen = new Set();

  for (const page of pages) {
    if (!page || page.status !== "publish" || !page.link) {
      continue;
    }

    const url = new URL(page.link);
    let route = url.pathname || "/";

    // Normalize as trailing-slash style to match WP local URLs.
    if (route !== "/" && !route.endsWith("/")) {
      route = `${route}/`;
    }

    if (!seen.has(route)) {
      seen.add(route);
      routes.push(route);
    }
  }

  // Ensure home is always first.
  routes.sort((a, b) => {
    if (a === "/") return -1;
    if (b === "/") return 1;
    return a.localeCompare(b);
  });

  return routes;
}

const stamp = nowStamp();
const outputDir = path.join("docs", "render-captures", `${stamp}-complete-snapshot`);
fs.mkdirSync(outputDir, { recursive: true });

const routes = await getPublishedRoutes();
const browser = await chromium.launch({ headless: true });

const manifest = {
  generatedAt: new Date().toISOString(),
  base,
  routes,
  profiles,
  outputDir,
  captures: [],
};

for (const profile of profiles) {
  const page = await browser.newPage({ viewport: profile.viewport });

  for (const route of routes) {
    const slug = slugFromRoute(route);
    const url = `${base}${route}`;

    await page.goto(url, { waitUntil: "domcontentloaded", timeout: 60000 });
    await page.waitForTimeout(1400);

    const pageMetrics = await page.evaluate(() => {
      const doc = document.documentElement;
      const body = document.body;
      const totalHeight = Math.max(
        doc ? doc.scrollHeight : 0,
        doc ? doc.offsetHeight : 0,
        body ? body.scrollHeight : 0,
        body ? body.offsetHeight : 0
      );
      return {
        title: document.title || "",
        viewportWidth: window.innerWidth,
        viewportHeight: window.innerHeight,
        totalHeight,
      };
    });

    const positions = buildScrollPositions(
      pageMetrics.totalHeight,
      pageMetrics.viewportHeight
    );

    const captureRecord = {
      profile: profile.key,
      route,
      slug,
      title: pageMetrics.title,
      viewport: profile.viewport,
      totalHeight: pageMetrics.totalHeight,
      slices: [],
    };

    let index = 1;
    for (const y of positions) {
      await page.evaluate((value) => window.scrollTo(0, value), y);
      await page.waitForTimeout(220);

      const filename = `${profile.key}-${slug}-slice-${String(index).padStart(2, "0")}-y${y}.png`;
      const output = path.join(outputDir, filename);
      await page.screenshot({ path: output, fullPage: false });
      console.log(`saved ${output}`);

      captureRecord.slices.push({
        index,
        y,
        file: filename,
      });
      index += 1;
    }

    const fullFilename = `${profile.key}-${slug}-fullpage.png`;
    const fullOutput = path.join(outputDir, fullFilename);
    await page.screenshot({ path: fullOutput, fullPage: true });
    console.log(`saved ${fullOutput}`);
    captureRecord.fullPage = fullFilename;

    if (profile.key === "mobile") {
      await page.evaluate(() => window.scrollTo(0, 0));
      await page.waitForTimeout(250);

      const menuToggle = await page.$("#menu_checkbox + label");
      if (menuToggle) {
        await menuToggle.click();
        await page.waitForTimeout(300);
        const menuOpenFilename = `${profile.key}-${slug}-menu-open-top.png`;
        const menuOpenOutput = path.join(outputDir, menuOpenFilename);
        await page.screenshot({ path: menuOpenOutput, fullPage: false });
        console.log(`saved ${menuOpenOutput}`);
        captureRecord.mobileMenuOpenTop = menuOpenFilename;

        // Close menu to keep subsequent page captures deterministic.
        await menuToggle.click();
        await page.waitForTimeout(250);
      }
    }

    manifest.captures.push(captureRecord);
  }

  await page.close();
}

await browser.close();

const manifestPath = path.join(outputDir, "manifest.json");
fs.writeFileSync(manifestPath, JSON.stringify(manifest, null, 2), "utf8");
console.log(`saved ${manifestPath}`);
