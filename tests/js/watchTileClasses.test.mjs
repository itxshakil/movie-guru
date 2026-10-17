import {test} from 'node:test';
import assert from 'node:assert/strict';
import {
    WATCH_TILE_FEATURED,
    WATCH_TILE_LINK,
    WATCH_TILE_NEUTRAL,
    WATCH_TILE_SHAPE,
} from '../../resources/js/Components/watchTileClasses.js';

test('the tile shape carries no colours, so neutral and featured tiles never conflict', () => {
    assert.doesNotMatch(WATCH_TILE_SHAPE, /(border|bg)-(gray|primary)-/);
    assert.match(WATCH_TILE_SHAPE, /rounded-xl/);
    assert.match(WATCH_TILE_SHAPE, /\bborder\b/);
});

test('the featured tile stays primary on hover in light and dark mode', () => {
    assert.doesNotMatch(WATCH_TILE_FEATURED, /gray/);
    assert.match(WATCH_TILE_FEATURED, /(^| )hover:bg-primary-/);
    assert.match(WATCH_TILE_FEATURED, /dark:hover:bg-primary-/);
});

test('the neutral tile uses gray borders and hover', () => {
    assert.doesNotMatch(WATCH_TILE_NEUTRAL, /primary/);
    assert.match(WATCH_TILE_NEUTRAL, /border-gray-200/);
});

test('the link carries the padding so the whole tile is clickable', () => {
    assert.match(WATCH_TILE_LINK, /flex-1/);
    assert.match(WATCH_TILE_LINK, /p-3/);
});
