import {test} from 'node:test';
import assert from 'node:assert/strict';
import {reloadWhenUpdated, routeLaunches} from '../../resources/js/Composables/appLifecycle.js';

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
const fakeLaunchQueue = () => ({consumer: null, setConsumer(fn) { this.consumer = fn; }});

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
