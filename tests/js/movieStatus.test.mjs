import {test} from 'node:test';
import assert from 'node:assert/strict';
import {movieStatusBadge, ratingSourceLabel} from '../../resources/js/Composables/movieStatus.js';

const today = new Date(2026, 9, 18);

test('a highly rated recent release is trending', () => {
    assert.equal(movieStatusBadge({imdbRating: '8.7', imdbVotes: '120,000', Released: '10 Oct 2026'}, today), 'Trending');
});

test('a recent release without top ratings is new', () => {
    assert.equal(movieStatusBadge({imdbRating: '6.1', imdbVotes: '2,000', Released: '01 Oct 2026'}, today), 'New');
});

test('an older title with many high votes is top rated', () => {
    assert.equal(movieStatusBadge({imdbRating: '8.8', imdbVotes: '2,877,173', Released: '16 Jul 2010'}, today), 'Top rated');
    assert.equal(movieStatusBadge({imdbRating: '8.1', imdbVotes: '150,000', Released: '16 Jul 2010'}, today), 'Top rated');
});

test('a very highly rated title with few votes is a hidden gem', () => {
    assert.equal(movieStatusBadge({imdbRating: '8.9', imdbVotes: '12,000', Released: '01 Jan 2001'}, today), 'Hidden gem');
});

test('an ordinary or unrated title has no badge', () => {
    assert.equal(movieStatusBadge({imdbRating: '7.0', imdbVotes: '50,000', Released: '01 Jan 2001'}, today), null);
    assert.equal(movieStatusBadge({imdbRating: 'N/A', imdbVotes: 'N/A', Released: 'N/A'}, today), null);
    assert.equal(movieStatusBadge({}, today), null);
});

test('rating sources are shortened only where a short name exists', () => {
    assert.equal(ratingSourceLabel('Internet Movie Database'), 'IMDb');
    assert.equal(ratingSourceLabel('Rotten Tomatoes'), 'Rotten Tomatoes');
    assert.equal(ratingSourceLabel('Metacritic'), 'Metacritic');
});
