/**
 * Build dist/ — everything the server needs, in the exact layout
 * deploy.sh expects (unzipped into www/dist, where cron.sh runs).
 *
 *   bun run scripts/build.ts          one-shot build
 *   bun run scripts/build.ts --watch  rebuild on change, then re-render
 *                                     the html locally via cron.sh
 *
 * CSS note: css/main.css contains url(./dist/img/...) — a RUNTIME web
 * path resolved by the browser against the page URL. Bun.build resolves
 * url() at build time and fails on it, so minification is a small
 * regex pass here, guarded by assertions that it changed nothing
 * structural (brace balance, url() count).
 */
import { Glob } from "bun";
import { cpSync, mkdirSync, rmSync, watch } from "node:fs";
import { basename, extname, join } from "node:path";
import sharp from "sharp";

const ROOT = join(import.meta.dir, "..");
const DIST = join(ROOT, "dist");

function minifyCss(css: string): string {
  const out = css
    .replace(/\/\*[\s\S]*?\*\//g, "")
    .replace(/\s+/g, " ")
    .replace(/\s*([{};:,>])\s*/g, "$1")
    .replace(/;}/g, "}")
    .trim();
  const count = (s: string, re: RegExp) => (s.match(re) ?? []).length;
  for (const [name, re] of [
    ["{", /{/g],
    ["}", /}/g],
    ["url(", /url\(/g],
  ] as const) {
    if (count(css, re) !== count(out, re)) {
      throw new Error(`css minify changed "${name}" count — refusing to ship`);
    }
  }
  return out;
}

async function copyGlob(pattern: string, destDir: string) {
  let n = 0;
  for (const file of new Glob(pattern).scanSync({ cwd: ROOT })) {
    cpSync(join(ROOT, file), join(destDir, basename(file)));
    n++;
  }
  return n;
}

async function buildImages(): Promise<[number, number, number]> {
  let files = 0,
    bytesIn = 0,
    bytesOut = 0;
  for (const file of new Glob("img/*").scanSync({ cwd: ROOT })) {
    const src = join(ROOT, file);
    const dest = join(DIST, "img", basename(file));
    const source = await Bun.file(src).bytes();
    const ext = extname(file).toLowerCase();
    let output = source;
    if (ext === ".jpg" || ext === ".jpeg") {
      output = new Uint8Array(await sharp(source).jpeg({ quality: 80, mozjpeg: true }).toBuffer());
    } else if (ext === ".png") {
      output = new Uint8Array(await sharp(source).png({ palette: true }).toBuffer());
    }
    // never ship a "compressed" file that got bigger
    if (output.byteLength >= source.byteLength) output = source;
    await Bun.write(dest, output);
    files++;
    bytesIn += source.byteLength;
    bytesOut += output.byteLength;
  }
  return [files, bytesIn, bytesOut];
}

async function build() {
  const started = Date.now();
  rmSync(DIST, { recursive: true, force: true });
  for (const dir of ["css", "img", "lib", "templates", "data"]) {
    mkdirSync(join(DIST, dir), { recursive: true });
  }

  let copied = 0;
  copied += await copyGlob("*.php", DIST);
  copied += await copyGlob("*.ico", DIST);
  copied += await copyGlob("sitemap.xml", DIST);
  copied += await copyGlob("cron.sh", DIST);
  copied += await copyGlob("lib/*.php", join(DIST, "lib"));
  copied += await copyGlob("templates/*.php", join(DIST, "templates"));
  copied += await copyGlob("data/*.json", join(DIST, "data"));

  let cssIn = 0,
    cssOut = 0;
  for (const file of new Glob("css/*.css").scanSync({ cwd: ROOT })) {
    const source = await Bun.file(join(ROOT, file)).text();
    const minified = minifyCss(source);
    await Bun.write(join(DIST, "css", basename(file)), minified);
    cssIn += source.length;
    cssOut += minified.length;
  }

  const [imgFiles, imgIn, imgOut] = await buildImages();

  console.log(
    `dist/ built in ${Date.now() - started}ms: ` +
      `${copied} files copied, css ${cssIn}B -> ${cssOut}B, ` +
      `${imgFiles} images ${imgIn}B -> ${imgOut}B`,
  );
}

const WATCHED = /^(css|lib|templates|data)\/|^[^/]+\.php$|^cron\.sh$/;

async function watchMode() {
  console.log("watching css/, lib/, templates/, *.php, cron.sh — ctrl-c to stop");
  let timer: ReturnType<typeof setTimeout> | null = null;
  watch(ROOT, { recursive: true }, (_event, filename) => {
    if (!filename || !WATCHED.test(filename)) return;
    if (timer) clearTimeout(timer);
    timer = setTimeout(async () => {
      try {
        await build();
        const cron = Bun.spawnSync(["sh", "cron.sh"], { cwd: ROOT });
        process.stdout.write(cron.stdout);
        if (cron.exitCode !== 0) {
          process.stderr.write(cron.stderr);
          console.error("cron.sh failed");
        }
      } catch (err) {
        console.error("BUILD FAILED:", err);
      }
    }, 300);
  });
}

try {
  await build();
} catch (err) {
  console.error("BUILD FAILED:", err);
  process.exit(1);
}
if (process.argv.includes("--watch")) {
  await watchMode();
}
