import { readFileSync, writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

// Reads Vite's build manifest and writes a flat list of every hashed asset
// URL, so the service worker (public/sw.js) can precache the current build
// without hardcoding filenames that change on every `npm run build`.

const rootDir = path.dirname(path.dirname(fileURLToPath(import.meta.url)));
const manifestPath = path.join(rootDir, 'public/build/manifest.json');
const outputPath = path.join(rootDir, 'public/build/sw-precache.json');

const manifest = JSON.parse(readFileSync(manifestPath, 'utf8'));

const assetUrls = [...new Set(Object.values(manifest).map((entry) => `/build/${entry.file}`))];

writeFileSync(outputPath, JSON.stringify(assetUrls));

console.log(`Generated ${outputPath} with ${assetUrls.length} asset(s).`);
