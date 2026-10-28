import type {Field, View} from '@wordpress/dataviews';
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
    const pinned = defaults.pinnedLast;
    const fields = view.fields ?? [];

    if (pinned === undefined || !fields.includes(pinned) || fields[fields.length - 1] === pinned) {
        return view;
    }

    return {...view, fields: [...fields.filter((id) => id !== pinned), pinned]};
}
