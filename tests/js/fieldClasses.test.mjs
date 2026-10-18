import {test} from 'node:test';
import assert from 'node:assert/strict';
import {FIELD_CLASSES} from '../../resources/js/Components/Ui/fieldClasses.js';

test('the shared field surface has the card radius, focus ring and readable placeholders in both themes', () => {
    assert.match(FIELD_CLASSES, /rounded-xl/);
    assert.match(FIELD_CLASSES, /focus:ring-primary-500\/30/);
    assert.match(FIELD_CLASSES, /placeholder:text-gray-500/);
    assert.match(FIELD_CLASSES, /dark:bg-gray-900/);
    assert.match(FIELD_CLASSES, /dark:placeholder:text-gray-400/);
});

test('the joined class string has no doubled or dangling spaces', () => {
    assert.equal(FIELD_CLASSES, FIELD_CLASSES.trim());
    assert.doesNotMatch(FIELD_CLASSES, / {2}/);
});
