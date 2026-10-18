import {test} from 'node:test';
import assert from 'node:assert/strict';
import {buttonClasses, buttonTag, chipClasses, chipPressed} from '../../resources/js/Components/Ui/buttonClasses.js';

test('primary md button has the shared shape and primary colours', () => {
    const classes = buttonClasses({});
    assert.match(classes, /rounded-xl/);
    assert.match(classes, /bg-primary-600/);
    assert.match(classes, /px-4 py-2\.5 text-sm/);
    assert.doesNotMatch(classes, /uppercase|tracking-widest/);
});

test('each variant and size maps to its own classes', () => {
    assert.match(buttonClasses({variant: 'secondary'}), /bg-gray-100/);
    assert.match(buttonClasses({variant: 'ghost'}), /bg-transparent/);
    assert.match(buttonClasses({variant: 'danger'}), /bg-red-600/);
    assert.match(buttonClasses({size: 'sm'}), /px-3 py-1\.5 text-xs/);
    assert.match(buttonClasses({size: 'lg'}), /px-6 py-3 text-base/);
    assert.match(buttonClasses({block: true}), /w-full/);
});

test('the icon size is a round 40px square with no text padding', () => {
    const classes = buttonClasses({variant: 'ghost', size: 'icon'});
    assert.match(classes, /size-10/);
    assert.match(classes, /rounded-full/);
    assert.doesNotMatch(classes, /rounded-xl|px-|py-|text-(xs|sm|base)\b/);
    assert.match(classes, /focus-visible:outline-2/);
});

test('text sizes keep the shared rounded-xl shape', () => {
    for (const size of ['sm', 'md', 'lg']) {
        assert.match(buttonClasses({size}), /rounded-xl/);
        assert.doesNotMatch(buttonClasses({size}), /rounded-full/);
    }
});

test('unknown variant or size falls back to the defaults', () => {
    assert.equal(buttonClasses({variant: 'nope', size: 'huge'}), buttonClasses({}));
});

test('a pressed toggle swaps its variant colours for its tone and keeps its size', () => {
    const watchlist = buttonClasses({variant: 'secondary', size: 'sm', pressed: true, pressedTone: 'primary'});
    assert.match(watchlist, /bg-primary-50 text-primary-700 ring-1/);
    assert.match(watchlist, /hover:bg-primary-100/);
    assert.match(watchlist, /dark:bg-primary-950/);
    assert.match(watchlist, /px-3 py-1\.5 text-xs/);
    assert.doesNotMatch(watchlist, /bg-gray-100|hover:bg-gray-200|dark:bg-gray-800/);

    const watched = buttonClasses({variant: 'secondary', pressed: true, pressedTone: 'success'});
    assert.match(watched, /bg-emerald-50 text-emerald-800 ring-1/);
    assert.doesNotMatch(watched, /bg-gray-100|bg-primary/);
});

test('an unpressed toggle keeps its variant and an unknown tone falls back to primary', () => {
    assert.equal(buttonClasses({variant: 'secondary', pressed: false, pressedTone: 'success'}), buttonClasses({variant: 'secondary'}));
    assert.equal(buttonClasses({pressed: true, pressedTone: 'nope'}), buttonClasses({pressed: true, pressedTone: 'primary'}));
    assert.doesNotMatch(buttonClasses({pressed: true}), /!/);
});

test('an external button renders as a plain anchor only with an href and while enabled', () => {
    assert.equal(buttonTag({href: 'https://wa.me/1', external: true}), 'external');
    assert.equal(buttonTag({href: '', external: true}), 'button');
    assert.equal(buttonTag({href: 'https://wa.me/1', external: true, disabled: true}), 'button');
    assert.equal(buttonTag({href: '/mood', external: false}), 'link');
});

test('a button renders as a link only with an href and while enabled', () => {
    assert.equal(buttonTag({href: '/mood'}), 'link');
    assert.equal(buttonTag({href: ''}), 'button');
    assert.equal(buttonTag({href: '/mood', disabled: true}), 'button');
    assert.equal(buttonTag({}), 'button');
});

test('chips switch colours when active and drop hover styles when static', () => {
    assert.match(chipClasses({active: true}), /bg-primary-600 text-white/);
    assert.match(chipClasses({}), /bg-white/);
    assert.doesNotMatch(chipClasses({interactive: false}), /hover:/);
    assert.match(chipClasses({size: 'sm'}), /text-\[11px\]/);
});

test('interactive chips get the shared focus ring and static chips do not', () => {
    assert.match(chipClasses({}), /focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500/);
    assert.doesNotMatch(chipClasses({interactive: false}), /focus-visible/);
});

test('only a button chip with an active state reports aria-pressed', () => {
    assert.equal(chipPressed({as: 'button', active: true}), true);
    assert.equal(chipPressed({as: 'button', active: false}), false);
    assert.equal(chipPressed({as: 'button'}), undefined);
    assert.equal(chipPressed({as: 'span', active: true}), undefined);
});
