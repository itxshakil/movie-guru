import {test} from 'node:test';
import assert from 'node:assert/strict';
import {isPlainClick, QUICK_VIEW_MAX_WIDTH, shouldOpenQuickView} from '../../resources/js/Components/cardClicks.js';

const click = (overrides = {}) => ({button: 0, metaKey: false, ctrlKey: false, shiftKey: false, altKey: false, ...overrides});

test('a primary click without modifiers is plain', () => {
    assert.equal(isPlainClick(click()), true);
});

test('any modifier key makes a click not plain', () => {
    for (const key of ['metaKey', 'ctrlKey', 'shiftKey', 'altKey']) {
        assert.equal(isPlainClick(click({[key]: true})), false, key);
    }
});

test('middle and right clicks are not plain', () => {
    assert.equal(isPlainClick(click({button: 1})), false);
    assert.equal(isPlainClick(click({button: 2})), false);
});

test('a plain click opens the quick view only below the breakpoint', () => {
    assert.equal(QUICK_VIEW_MAX_WIDTH, 1024);
    assert.equal(shouldOpenQuickView(click(), 390), true);
    assert.equal(shouldOpenQuickView(click(), 1023), true);
    assert.equal(shouldOpenQuickView(click(), 1024), false);
    assert.equal(shouldOpenQuickView(click(), 1440), false);
});

test('a modified or middle click follows the link at every width', () => {
    for (const width of [390, 1023, 1024, 1440]) {
        assert.equal(shouldOpenQuickView(click({metaKey: true}), width), false);
        assert.equal(shouldOpenQuickView(click({ctrlKey: true}), width), false);
        assert.equal(shouldOpenQuickView(click({button: 1}), width), false);
    }
});
