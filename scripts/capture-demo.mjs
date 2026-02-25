import { chromium } from "playwright";
import { execSync } from "child_process";
import { readdirSync, statSync } from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, "..");
const VIDEO_DIR = path.join(ROOT, ".demo-video");
const OUTPUT_GIF = path.join(ROOT, "docs", "images", "demo.gif");

const WIDTH = 1280;
const HEIGHT = 800;
const BASE_URL = process.argv[2] || "http://127.0.0.1:8000";

function sleep(ms) {
  return new Promise((r) => setTimeout(r, ms));
}

async function main() {
  console.log("Launching browser...");
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: WIDTH, height: HEIGHT },
    colorScheme: "dark",
    recordVideo: { dir: VIDEO_DIR, size: { width: WIDTH, height: HEIGHT } },
  });
  const page = await context.newPage();

  // 1. Navigate to disciplines page
  console.log("Navigating to disciplines...");
  await page.goto(`${BASE_URL}/disciplines`, { waitUntil: "networkidle" });
  await sleep(1500);

  // 2. Select all disciplines
  console.log("Selecting all disciplines...");
  await page.click('[wire\\:click="selectAll"]');
  await sleep(800);

  // 3. Save selection
  await page.click('[wire\\:click="save"]');
  await page.waitForSelector("text=Selection saved", { timeout: 5000 });
  await sleep(1000);

  // 4. Click into a discipline to show sources (Math)
  console.log("Viewing Math sources...");
  await page.click('a[href*="/disciplines/math"]');
  await page.waitForURL("**/disciplines/math");
  await sleep(1500);

  // 5. Scroll to show sources
  await page.evaluate(() => window.scrollBy({ top: 300, behavior: "smooth" }));
  await sleep(1000);
  await page.evaluate(() => window.scrollTo({ top: 0, behavior: "smooth" }));
  await sleep(800);

  // 6. Select just a couple fast sources for the demo
  console.log("Configuring sources...");
  await page.click('[wire\\:click="selectNone"]');
  await sleep(500);
  await page.click('[wire\\:click="toggleSource(\'biorxiv_recent\')"]');
  await sleep(400);
  await page.click('[wire\\:click="toggleSource(\'medrxiv_recent\')"]');
  await sleep(400);
  await page.click('[wire\\:click="save"]');
  await page.waitForSelector("text=Sources updated", { timeout: 5000 });
  await sleep(1000);

  // 7. Navigate to digest page
  console.log("Navigating to digest...");
  await page.click('a[href*="/digest"]');
  await page.waitForURL("**/digest");
  await sleep(1500);

  // 8. Click "Skip cache" checkbox for fresh results
  const skipCache = page.locator('input[type="checkbox"]').first();
  await skipCache.check();
  await sleep(500);

  // 9. Generate digest
  console.log("Generating digest (this takes 30-60s)...");
  await page.click('[wire\\:click="generate"]');

  // 10. Wait for results to stream in (watch for ELI5 sections)
  await page.waitForSelector("text=ELI5", { timeout: 120000 });
  console.log("First results appeared!");
  await sleep(3000);

  // 11. Scroll down to show the color-coded sections
  console.log("Scrolling through results...");
  await page.evaluate(() => window.scrollBy({ top: 400, behavior: "smooth" }));
  await sleep(2000);
  await page.evaluate(() => window.scrollBy({ top: 400, behavior: "smooth" }));
  await sleep(2000);

  // 12. Save a paper (click a star)
  console.log("Saving a paper...");
  const saveBtn = page.locator('button[wire\\:click*="toggleSave"]').first();
  if ((await saveBtn.count()) > 0) {
    await saveBtn.click();
    await sleep(1000);
  }

  // 13. Scroll back up to show the full view
  await page.evaluate(() => window.scrollTo({ top: 0, behavior: "smooth" }));
  await sleep(1500);

  // Done — close to finalize video
  console.log("Finalizing recording...");
  await context.close();
  await browser.close();

  // Find the recorded webm
  const videos = readdirSync(VIDEO_DIR).filter((f) => f.endsWith(".webm"));
  if (videos.length === 0) {
    console.error("No video recorded");
    process.exit(1);
  }
  const videoPath = path.join(VIDEO_DIR, videos[0]);

  // Convert to GIF with ffmpeg (speed up 3x to keep GIF short)
  console.log("Converting to GIF (3x speed)...");
  execSync(
    `ffmpeg -y -i "${videoPath}" -vf "setpts=0.33*PTS,fps=12,scale=${WIDTH}:-1:flags=lanczos,split[s0][s1];[s0]palettegen=max_colors=128[p];[s1][p]paletteuse=dither=bayer:bayer_scale=3" -loop 0 "${OUTPUT_GIF}"`,
    { stdio: "inherit" }
  );

  // Cleanup
  execSync(`rm -rf "${VIDEO_DIR}"`);

  const size = (statSync(OUTPUT_GIF).size / 1024 / 1024).toFixed(1);
  console.log(`Done! ${OUTPUT_GIF} (${size}MB)`);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
