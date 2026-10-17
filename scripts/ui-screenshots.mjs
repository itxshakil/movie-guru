// Screenshots pages at phone and desktop widths in light and dark mode through the Chrome
// DevTools protocol, and fails when a page is wider than its viewport.
// Usage: node scripts/ui-screenshots.mjs <baseUrl> <outDir> [path ...]
import {spawn} from 'node:child_process';
import {mkdirSync, writeFileSync} from 'node:fs';
import {join} from 'node:path';

const CHROME = process.env.CHROME_BIN ?? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const DEFAULT_PATHS = ['/', '/search?s=inception', '/i/tt1375666', '/watchlist', '/watchlist?tab=watched', '/mood',
    '/movie-match', '/contact', '/login', '/terms'];
const VIEWPORTS = {mobile: {width: 390, height: 844, mobile: true}, desktop: {width: 1440, height: 900, mobile: false}};
const SCHEMES = ['light', 'dark'];
const MAX_HEIGHT = 8000;

const [baseUrl, outDir, ...paths] = process.argv.slice(2);
if (!baseUrl || !outDir) {
    console.error('Usage: node scripts/ui-screenshots.mjs <baseUrl> <outDir> [path ...]');
    process.exit(2);
}
mkdirSync(outDir, {recursive: true});

const port = 9300 + Math.floor(Math.random() * 500);
const chrome = spawn(CHROME, ['--headless=new', '--disable-gpu', '--hide-scrollbars', `--remote-debugging-port=${port}`,
    `--user-data-dir=${join(outDir, '.profile')}`, 'about:blank'], {stdio: 'ignore'});
let spawnError = null;
chrome.on('error', (error) => {
    spawnError = error;
});

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

async function connect() {
    for (let attempt = 0; attempt < 50; attempt++) {
        await sleep(200);
        if (spawnError) {
            throw spawnError;
        }
        try {
            const targets = await (await fetch(`http://127.0.0.1:${port}/json`)).json();
            const page = targets.find((target) => target.type === 'page');
            if (page) {
                return page.webSocketDebuggerUrl;
            }
        } catch {
            // Chrome is still starting; the loop retries until the attempts run out.
        }
    }
    throw new Error('Chrome did not expose a page target');
}

let socket;
let nextId = 0;
const pending = new Map();
const listeners = new Set();
const rejectPending = (reason) => {
    pending.forEach((settle) => settle.reject(new Error(reason)));
    pending.clear();
};
const send = (method, params = {}) => new Promise((resolve, reject) => {
    const id = ++nextId;
    pending.set(id, {
        reject,
        resolve: (message) => {
            if (message.error) {
                reject(new Error(`${method} failed: ${message.error.message}`));
                return;
            }
            resolve(message);
        },
    });
    socket.send(JSON.stringify({id, method, params}));
});

const STATUS_PROBE = `performance.getEntriesByType('navigation')[0]?.responseStatus ?? null`;

const OVERFLOW_PROBE = `(() => {
    const inScroller = (el) => { for (let p = el.parentElement; p; p = p.parentElement) {
        const o = getComputedStyle(p).overflowX; if (o === 'auto' || o === 'scroll' || o === 'hidden' || o === 'clip') return true; } return false; };
    const wide = [...document.querySelectorAll('body *')]
        .filter((el) => el.getBoundingClientRect().right > innerWidth + 1 && !inScroller(el))
        .slice(0, 10)
        .map((el) => el.tagName.toLowerCase() + (el.className && typeof el.className === 'string' ? '.' + el.className.trim().split(/\\s+/).slice(0, 3).join('.') : ''));
    return {innerWidth, scrollWidth: document.documentElement.scrollWidth, height: document.documentElement.scrollHeight, wide};
})()`;

let failed = false;
try {
    socket = new WebSocket(await connect());
    await new Promise((resolve, reject) => {
        socket.addEventListener('open', resolve);
        socket.addEventListener('error', () => reject(new Error('Could not open the DevTools socket')));
    });
    socket.addEventListener('message', (event) => {
        const message = JSON.parse(event.data);
        if (message.id && pending.has(message.id)) {
            pending.get(message.id).resolve(message);
            pending.delete(message.id);
            return;
        }
        listeners.forEach((listener) => listener(message));
    });
    socket.addEventListener('close', () => rejectPending('DevTools socket closed'));
    socket.addEventListener('error', () => rejectPending('DevTools socket error'));
    await send('Page.enable');
    for (const path of paths.length ? paths : DEFAULT_PATHS) {
        for (const [viewportName, viewport] of Object.entries(VIEWPORTS)) {
            for (const scheme of SCHEMES) {
                // Without this reset, a full-page capture after a mobile-to-desktop switch hangs.
                await send('Emulation.clearDeviceMetricsOverride');
                await send('Emulation.setDeviceMetricsOverride', {width: viewport.width, height: viewport.height,
                    deviceScaleFactor: 1, mobile: viewport.mobile});
                await send('Emulation.setTouchEmulationEnabled', {enabled: viewport.mobile});
                await send('Emulation.setEmulatedMedia', {features: [{name: 'prefers-color-scheme', value: scheme},
                    // Scroll-driven .scroll-reveal cards stay invisible in full-page shots unless motion is reduced.
                    {name: 'prefers-reduced-motion', value: 'reduce'}]});
                const loaded = new Promise((resolve) => {
                    const onLoad = (message) => {
                        if (message.method === 'Page.loadEventFired') {
                            listeners.delete(onLoad);
                            resolve();
                        }
                    };
                    listeners.add(onLoad);
                });
                await send('Page.navigate', {url: new URL(path, baseUrl).href});
                await Promise.race([loaded, sleep(20000)]);
                await sleep(2500);

                const probe = (await send('Runtime.evaluate', {expression: OVERFLOW_PROBE, returnByValue: true})).result.result.value;
                const status = (await send('Runtime.evaluate', {expression: STATUS_PROBE, returnByValue: true}))
                    .result.result.value;
                const overflow = probe.scrollWidth > viewport.width ? probe.wide : [];
                failed ||= overflow.length > 0 || probe.scrollWidth > viewport.width;
                console.log(JSON.stringify({path, viewport: viewportName, scheme, status, scrollWidth: probe.scrollWidth,
                    innerWidth: probe.innerWidth, overflow}));

                const shot = await send('Page.captureScreenshot', {format: 'png', captureBeyondViewport: true,
                    clip: {x: 0, y: 0, width: viewport.width, height: Math.min(probe.height, MAX_HEIGHT), scale: 1}});
                const slug = path.replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '') || 'home';
                writeFileSync(join(outDir, `${slug}-${viewportName}-${scheme}.png`), Buffer.from(shot.result.data, 'base64'));
            }
        }
    }
} catch (error) {
    console.error(error);
    failed = true;
    process.exitCode = 2;
} finally {
    chrome.kill('SIGKILL');
}
process.exit(process.exitCode ?? (failed ? 1 : 0));
