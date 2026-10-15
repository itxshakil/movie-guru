import {test} from 'node:test';
import assert from 'node:assert/strict';
import {
    applyLibraryChange,
    chunk,
    prepareGuestImport,
    splitLibrary,
    toLibraryItem,
    upgradeLegacyItem,
} from '../../resources/js/Composables/libraryState.js';

const now = new Date('2026-10-15T12:00:00.000Z');
const movie = {imdb_id: 'tt1375666', title: 'Inception', poster: 'p.jpg', year: 2010};

test('a new item starts on the watchlist', () => {
    assert.deepEqual(toLibraryItem(movie, now), {
        imdb_id: 'tt1375666',
        title: 'Inception',
        poster: 'p.jpg',
        year: '2010',
        status: 'to_watch',
        reaction: null,
        watched_at: null,
        saved_at: '2026-10-15T12:00:00.000Z',
    });
});

test('legacy rows with numeric saved_at become to watch items', () => {
    const upgraded = upgradeLegacyItem({imdb_id: 'tt1', title: 'A', poster: null, year: '1999', saved_at: 1_700_000_000_000});
    assert.equal(upgraded.status, 'to_watch');
    assert.equal(upgraded.reaction, null);
    assert.equal(upgraded.watched_at, null);
    assert.equal(upgraded.saved_at, new Date(1_700_000_000_000).toISOString());
});

test('legacy rows without saved_at still get a valid date', () => {
    assert.equal(upgradeLegacyItem({imdb_id: 'tt1', title: 'A'}).saved_at, new Date(0).toISOString());
});

test('marking watched stamps watched_at and keeps a given reaction', () => {
    const watched = applyLibraryChange(toLibraryItem(movie, now), {type: 'mark-watched', reaction: 'liked'}, now);
    assert.equal(watched.status, 'watched');
    assert.equal(watched.reaction, 'liked');
    assert.equal(watched.watched_at, now.toISOString());
});

test('marking an already watched title keeps its watched date', () => {
    const first = applyLibraryChange(toLibraryItem(movie, now), {type: 'mark-watched'}, now);
    const later = applyLibraryChange(first, {type: 'mark-watched', reaction: 'disliked'}, new Date('2026-12-01T00:00:00Z'));
    assert.equal(later.watched_at, now.toISOString());
    assert.equal(later.reaction, 'disliked');
});

test('setting a reaction requires the title to be watched', () => {
    assert.throws(
        () => applyLibraryChange(toLibraryItem(movie, now), {type: 'set-reaction', reaction: 'liked'}, now),
        /A reaction needs the title to be watched/,
    );
});

test('unknown reactions are refused', () => {
    const watched = applyLibraryChange(toLibraryItem(movie, now), {type: 'mark-watched'}, now);
    assert.throws(() => applyLibraryChange(watched, {type: 'set-reaction', reaction: 'meh'}, now), /Unknown reaction/);
});

test('going back to the watchlist clears reaction and watched date', () => {
    const watched = applyLibraryChange(toLibraryItem(movie, now), {type: 'mark-watched', reaction: 'liked'}, now);
    const back = applyLibraryChange(watched, {type: 'add-to-watchlist'}, now);
    assert.equal(back.status, 'to_watch');
    assert.equal(back.reaction, null);
    assert.equal(back.watched_at, null);
});

test('changes never mutate the original item', () => {
    const item = toLibraryItem(movie, now);
    applyLibraryChange(item, {type: 'mark-watched'}, now);
    assert.equal(item.status, 'to_watch');
});

test('the library splits into tabs, newest first', () => {
    const a = {...toLibraryItem({...movie, imdb_id: 'tt1'}, new Date('2026-01-01T00:00:00Z'))};
    const b = {...toLibraryItem({...movie, imdb_id: 'tt2'}, new Date('2026-02-01T00:00:00Z'))};
    const c = applyLibraryChange(toLibraryItem({...movie, imdb_id: 'tt3'}, now), {type: 'mark-watched'}, new Date('2026-03-01T00:00:00Z'));
    const d = applyLibraryChange(toLibraryItem({...movie, imdb_id: 'tt4'}, now), {type: 'mark-watched'}, new Date('2026-04-01T00:00:00Z'));

    const {toWatch, watched} = splitLibrary([a, c, b, d]);
    assert.deepEqual(toWatch.map((item) => item.imdb_id), ['tt2', 'tt1']);
    assert.deepEqual(watched.map((item) => item.imdb_id), ['tt4', 'tt3']);
});

test('the guest import keeps valid rows and drops what the server would reject', () => {
    const watched = applyLibraryChange(toLibraryItem(movie, now), {type: 'mark-watched', reaction: 'liked'}, now);
    const rows = prepareGuestImport([
        {...watched, saved_at: 'x'},
        {...toLibraryItem({...movie, imdb_id: 'TT0816692', title: 'Interstellar'}, now)},
        {...toLibraryItem({...movie, imdb_id: 'tt0816692', title: 'Duplicate'}, now)},
        {...toLibraryItem({...movie, imdb_id: 'not-an-id'}, now)},
        {...toLibraryItem({...movie, imdb_id: 'tt0068646', title: '   '}, now)},
    ]);

    assert.deepEqual(rows, [
        {
            imdb_id: 'tt1375666',
            title: 'Inception',
            poster: 'p.jpg',
            year: '2010',
            status: 'watched',
            reaction: 'liked',
            watched_at: now.toISOString(),
        },
        {
            imdb_id: 'tt0816692',
            title: 'Interstellar',
            poster: 'p.jpg',
            year: '2010',
            status: 'to_watch',
            reaction: null,
            watched_at: null,
        },
    ]);
});

test('the guest import trims fields to the server limits', () => {
    const [row] = prepareGuestImport([{
        ...toLibraryItem(movie, now),
        title: `${'a'.repeat(254)}😀😀`,
        year: '2010-2011-2012',
        poster: `https://example.com/${'p'.repeat(500)}`,
        reaction: 'liked',
    }]);

    assert.equal(Array.from(row.title).length, 255);
    assert.ok(row.title.endsWith('😀'));
    assert.equal(row.year, null);
    assert.equal(row.poster, null);
    assert.equal(row.reaction, null);
});

test('large imports are split into chunks of 500', () => {
    const items = Array.from({length: 1001}, (_, index) => index);
    assert.deepEqual(chunk(items).map((part) => part.length), [500, 500, 1]);
    assert.deepEqual(chunk([]), []);
});
