import {beforeEach, test} from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import vm from 'node:vm';

// sw.js is a classic worker script, so it runs in a sandbox with fake browser APIs.
const source = readFileSync(new URL('../../public/sw.js', import.meta.url), 'utf8');
const origin = 'https://movieguru.test';
const DAY = 24 * 60 * 60 * 1000;

let worker;
let queue;
let responses;
let windows;
let opened;

beforeEach(() => {
    queue = new Map();
    responses = {};
    windows = [];
    opened = [];

    const store = {
        getAll: () => ({result: [...queue.values()]}),
        delete: (url) => queue.delete(url) && {result: undefined},
    };

    worker = vm.createContext({
        self: {addEventListener: () => {}, location: {origin}, registration: {}},
        BroadcastChannel: class {
            postMessage() {}
        },
        Notification: {permission: 'denied'},
        clients: {
            matchAll: async () => windows,
            openWindow: async (url) => opened.push(url),
        },
        caches: {open: async () => ({})},
        fetch: async (request) => new Response('results', {status: responses[request.url] ?? 200}),
        Request,
        Response,
        Headers,
        URL,
        console,
    });
    vm.runInContext(source, worker);
    worker.runTransaction = async (name, mode, operation) => operation(store)?.result;
    worker.putTaggedResponse = async () => {};
});

const queueSearch = (query, queuedAt = Date.now()) => {
    const url = `${origin}/search?s=${query}`;
    queue.set(url, {url, queuedAt});
    return url;
};

test('a search the server refuses is removed instead of retried', async () => {
    const refused = queueSearch('bad');
    responses[refused] = 422;

    await worker.processSearchQueue();

    assert.equal(queue.size, 0);
});

test('a failing search makes Background Sync retry', async () => {
    responses[queueSearch('dune')] = 503;

    await assert.rejects(worker.processSearchQueue());
});

test('the last retry resolves and keeps the search for the next online event', async () => {
    const failing = queueSearch('dune');
    responses[failing] = 503;

    await worker.processSearchQueue({lastChance: true});

    assert.deepEqual([...queue.keys()], [failing]);
});

test('searches queued more than three days ago are dropped without a fetch', async () => {
    const stale = queueSearch('old', Date.now() - 4 * DAY);
    responses[stale] = 503;

    await worker.processSearchQueue();

    assert.equal(queue.size, 0);
});

test('a relative notification link opens that page', async () => {
    await worker.openNotificationTarget(worker.notificationTarget('/search?s=dune'));

    assert.deepEqual(opened, [`${origin}/search?s=dune`]);
});

test('a link to another site opens the home page', () => {
    assert.equal(worker.notificationTarget('https://evil.example/phish'), `${origin}/`);
});

test('an open window on another page is reused instead of opening a new one', async () => {
    const navigated = [];
    const appWindow = {url: `${origin}/trending`, navigate: async (url) => navigated.push(url)};
    appWindow.focus = async () => appWindow;
    windows = [appWindow];

    await worker.openNotificationTarget(`${origin}/search?s=dune`);

    assert.deepEqual(navigated, [`${origin}/search?s=dune`]);
    assert.deepEqual(opened, []);
});
