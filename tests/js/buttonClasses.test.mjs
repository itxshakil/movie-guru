import {test} from 'node:test';
import assert from 'node:assert/strict';
import {buttonClasses, buttonTag, chipClasses} from '../../resources/js/Components/Ui/buttonClasses.js';

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

test('unknown variant or size falls back to the defaults', () => {
    assert.equal(buttonClasses({variant: 'nope', size: 'huge'}), buttonClasses({}));
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
