import {test} from 'node:test';
import assert from 'node:assert/strict';
import {shareTitle} from '../../resources/js/Composables/shareTitle.js';
import {reloadWhenUpdated, routeLaunches} from '../../resources/js/Composables/appLifecycle.js';

const title = {title: 'Paterson', url: 'https://movieguru.test/movie/tt5247022'};

test('sharing uses the native share sheet when the browser has one', async () => {
    const shared = [];
    const notices = [];

    const outcome = await shareTitle(title, {
        nav: {share: async (data) => shared.push(data)},
        notify: (message) => notices.push(message),
    });

    assert.equal(outcome, 'shared');
    assert.deepEqual(shared, [{title: 'Paterson', text: 'Check out Paterson on Movie Guru!', url: title.url}]);
    assert.deepEqual(notices, []);
});

test('without a share sheet the link is copied and the toast says so', async () => {
    const copied = [];
    const notices = [];

    const outcome = await shareTitle(title, {
        nav: {clipboard: {writeText: async (text) => copied.push(text)}},
        notify: (message, level) => notices.push([message, level]),
    });

    assert.equal(outcome, 'copied');
    assert.deepEqual(copied, [title.url]);
    assert.deepEqual(notices, [['Link copied', 'success']]);
});

test('closing the share sheet is not an error', async () => {
    const notices = [];
    const abort = Object.assign(new Error('cancelled'), {name: 'AbortError'});

    const outcome = await shareTitle(title, {
        nav: {share: async () => { throw abort; }},
        notify: (message) => notices.push(message),
    });

    assert.equal(outcome, 'cancelled');
    assert.deepEqual(notices, []);
});

test('when neither sharing nor copying works, the toast says so', async () => {
    const notices = [];

    const outcome = await shareTitle(title, {
        nav: {clipboard: {writeText: async () => { throw new Error('denied'); }}},
        notify: (message, level) => notices.push([message, level]),
    });

    assert.equal(outcome, 'failed');
    assert.deepEqual(notices, [['Could not share or copy the link', 'danger']]);
});

/** A minimal stand-in for navigator.serviceWorker: an event target with a settable controller. */
const fakeServiceWorker = (controller) => {
    const container = new EventTarget();
    container.controller = controller;
    return container;
};

test('an open window reloads once when a new worker takes control', () => {
    const container = fakeServiceWorker({});
    let reloads = 0;
    reloadWhenUpdated(container, () => reloads++);

    container.dispatchEvent(new Event('controllerchange'));
    container.dispatchEvent(new Event('controllerchange'));

    assert.equal(reloads, 1);
});

test('the first visit does not reload when the worker first takes control', () => {
    const container = fakeServiceWorker(null);
    let reloads = 0;
    reloadWhenUpdated(container, () => reloads++);

    container.dispatchEvent(new Event('controllerchange'));

    assert.equal(reloads, 0);
});

test('a window opened on the first visit still reloads for a later update', () => {
    const container = fakeServiceWorker(null);
    let reloads = 0;
    reloadWhenUpdated(container, () => reloads++);

    container.dispatchEvent(new Event('controllerchange'));
    container.dispatchEvent(new Event('controllerchange'));

    assert.equal(reloads, 1);
});

/** launchQueue hands the consumer one LaunchParams per launch. */
const fakeLaunchQueue = () => {
    const queue = {consumer: null, setConsumer(fn) { this.consumer = fn; }};
    return queue;
};

test('a launch into the running window routes there without a full reload', () => {
    const queue = fakeLaunchQueue();
    const visits = [];
    routeLaunches(queue, (url) => visits.push(url), () => 'https://movieguru.test/');

    queue.consumer({targetURL: 'https://movieguru.test/watchlist?utm_source=watchlist_shortcut'});

    assert.deepEqual(visits, ['https://movieguru.test/watchlist?utm_source=watchlist_shortcut']);
});

test('the launch that opened this window is not visited a second time', () => {
    const queue = fakeLaunchQueue();
    const visits = [];
    routeLaunches(queue, (url) => visits.push(url), () => 'https://movieguru.test/search?s=dune');

    queue.consumer({targetURL: 'https://movieguru.test/search?s=dune'});
    queue.consumer({});

    assert.deepEqual(visits, []);
});

test('browsers without launchQueue are left alone', () => {
    assert.doesNotThrow(() => routeLaunches(undefined, () => {}, () => ''));
});
