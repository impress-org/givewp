import type {Field, View} from '@wordpress/dataviews';
import {insertAtDefaultPosition, withPinnedLast} from './fieldOrder';
import type {ScreenDefaults} from './types';

/**
 * Marks the screen's locked fields (the identifying column and the row actions) as not hideable, so
 * neither the view options nor the column header menu offer to hide them.
 *
 * @since TBD
 */
export function lockFields<Item>(fields: Field<Item>[], defaults: ScreenDefaults): Field<Item>[] {
    return fields.map((field) => (defaults.lockedFields.includes(field.id) ? {...field, enableHiding: false} : field));
}

/**
 * Keeps the pinned field (the legacy row actions) last after a column is moved.
 *
 * @since TBD
 */
export function keepPinnedLast<V extends View>(view: V, defaults: ScreenDefaults): V {
    const fields = view.fields ?? [];
    const ordered = withPinnedLast(fields, defaults.pinnedLast);

    return ordered === fields ? view : {...view, fields: ordered};
}

/**
 * Puts a column the admin just showed back at its default position among the shown columns, because the
 * view options add it at the end. Columns that stay shown keep the order the admin gave them, so hiding
 * a column and showing it again leaves the layout as it was.
 *
 * @since TBD
 */
export function placeShownFields<V extends View>(next: V, previous: View, defaults: ScreenDefaults): V {
    const before = previous.fields ?? [];
    const after = next.fields ?? [];
    const shown = after.filter((id) => !before.includes(id));

    if (shown.length === 0) {
        return next;
    }

    const fields = shown.reduce(
        (placed, id) => insertAtDefaultPosition(placed, id, defaults.fieldOrder),
        after.filter((id) => before.includes(id))
    );

    return {...next, fields};
}
