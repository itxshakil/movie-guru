import {test} from 'node:test';
import assert from 'node:assert/strict';
import {shareTitle} from '../../resources/js/Composables/shareTitle.js';

const movie = {title: 'Paterson', url: 'https://movieguru.test/movie/tt5247022'};

test('sharing uses the native share sheet when the browser has one', async () => {
    const shared = [];
    const notices = [];

    const outcome = await shareTitle(movie, {
        nav: {share: async (data) => shared.push(data)},
        notify: (message) => notices.push(message),
    });

    assert.equal(outcome, 'shared');
    assert.deepEqual(shared, [{title: 'Paterson', text: 'Check out Paterson on Movie Guru!', url: movie.url}]);
    assert.deepEqual(notices, []);
});

test('without a share sheet the link is copied and the toast says so', async () => {
    const copied = [];
    const notices = [];

    const outcome = await shareTitle(movie, {
        nav: {clipboard: {writeText: async (text) => copied.push(text)}},
        notify: (message, level) => notices.push([message, level]),
    });

    assert.equal(outcome, 'copied');
    assert.deepEqual(copied, [movie.url]);
    assert.deepEqual(notices, [['Link copied', 'success']]);
});

test('closing the share sheet is not an error', async () => {
    const notices = [];
    const abort = Object.assign(new Error('cancelled'), {name: 'AbortError'});

    const outcome = await shareTitle(movie, {
        nav: {share: async () => { throw abort; }},
        notify: (message) => notices.push(message),
    });

    assert.equal(outcome, 'cancelled');
    assert.deepEqual(notices, []);
});

test('when neither sharing nor copying works, the toast says so', async () => {
    const notices = [];

    const outcome = await shareTitle(movie, {
        nav: {clipboard: {writeText: async () => { throw new Error('denied'); }}},
        notify: (message, level) => notices.push([message, level]),
    });

    assert.equal(outcome, 'failed');
    assert.deepEqual(notices, [['Could not share or copy the link', 'danger']]);
});

test('a share sheet that fails for another reason falls back to copying the link', async () => {
    const copied = [];
    const notices = [];

    const outcome = await shareTitle(movie, {
        nav: {
            share: async () => { throw new Error('not allowed'); },
            clipboard: {writeText: async (text) => copied.push(text)},
        },
        notify: (message) => notices.push(message),
    });

    assert.equal(outcome, 'copied');
    assert.deepEqual(copied, [movie.url]);
    assert.deepEqual(notices, ['Link copied']);
});
