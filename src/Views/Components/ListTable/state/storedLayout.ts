import {snapPerPage} from '../adapters/viewToParameters';
import {insertAtDefaultPosition, withPinnedLast} from './fieldOrder';
import type {ListTableDensity, ListTableView, ScreenDefaults, StoredLayout, ViewLayoutPart} from './types';

/**
 * The densities DataViews offers.
 *
 * @since TBD
 */
const DENSITIES: ListTableDensity[] = ['compact', 'balanced', 'comfortable'];

const isRecord = (value: unknown): value is Record<string, unknown> =>
    typeof value === 'object' && value !== null && !Array.isArray(value);

const isDensity = (value: unknown): value is ListTableDensity => DENSITIES.includes(value as ListTableDensity);

const sameList = (a: string[], b: string[]) => a.length === b.length && a.every((id, index) => id === b[index]);

/**
 * The layout part of a view, with the defaults filling what the view leaves out.
 */
function layoutOf(view: ListTableView, defaults: ScreenDefaults): ViewLayoutPart {
    return {
        fields: view.fields ?? defaults.fields,
        density: view.layout?.density ?? defaults.density,
        perPage: view.perPage ?? defaults.perPage,
    };
}

/**
 * The visible fields in the order the defaults would show them.
 */
function defaultOrderOf(visible: string[], defaults: ScreenDefaults): string[] {
    return defaults.fieldOrder.filter((id) => visible.includes(id));
}

/**
 * The fields whose visibility differs from their default. Locked fields are never a choice.
 */
function visibilityChanges(visible: string[], defaults: ScreenDefaults): Record<string, boolean> {
    return Object.fromEntries(
        defaults.fieldOrder
            .filter((id) => !defaults.lockedFields.includes(id))
            .filter((id) => visible.includes(id) !== defaults.fields.includes(id))
            .map((id) => [id, visible.includes(id)])
    );
}

/**
 * The record to store for a view: only what differs from the screen defaults, so a column the user
 * never changed keeps following its default (a column hidden and shown again counts as never changed).
 *
 * @since TBD
 */
export function toStoredLayout(view: ListTableView, defaults: ScreenDefaults): StoredLayout {
    const layout = layoutOf(view, defaults);
    const visible = layout.fields.filter((id) => defaults.fieldOrder.includes(id));
    const order = visible.filter((id) => id !== defaults.pinnedLast);
    const visibility = visibilityChanges(visible, defaults);

    return {
        version: 1,
        ...(Object.keys(visibility).length > 0 && {visibility}),
        ...(!sameList(order, defaultOrderOf(order, defaults)) && {order}),
        ...(layout.density !== defaults.density && {density: layout.density}),
        ...(layout.perPage !== defaults.perPage && {perPage: layout.perPage}),
    };
}

/**
 * Whether a field is visible: locked fields always, then the stored choice, then the default.
 */
function isVisible(id: string, visibility: Record<string, unknown>, defaults: ScreenDefaults): boolean {
    if (defaults.lockedFields.includes(id)) {
        return true;
    }

    const stored = visibility[id];

    return typeof stored === 'boolean' ? stored : defaults.fields.includes(id);
}

/**
 * Keeps the stored order of the fields it names, and puts each other visible field at its default
 * position. The pinned field goes last.
 */
function mergeOrder(storedOrder: unknown, visible: string[], defaults: ScreenDefaults): string[] {
    const named = Array.isArray(storedOrder) ? storedOrder : [];
    const ordered = visible.filter((id) => named.includes(id)).sort((a, b) => named.indexOf(a) - named.indexOf(b));
    const merged = visible
        .filter((id) => !ordered.includes(id))
        .reduce((fields, id) => insertAtDefaultPosition(fields, id, defaults.fieldOrder), ordered);

    return withPinnedLast(merged, defaults.pinnedLast);
}

/**
 * Applies a stored record to the screen defaults. The record comes from the page, so it is read
 * tolerantly: another version or shape gives the defaults, unknown ids are ignored, locked fields stay
 * visible, and an invalid density or page size falls back to the default.
 *
 * @since TBD
 */
export function applyStoredLayout(defaults: ScreenDefaults, stored: unknown): ViewLayoutPart {
    const fallback: ViewLayoutPart = {fields: defaults.fields, density: defaults.density, perPage: defaults.perPage};

    if (!isRecord(stored) || stored.version !== 1) {
        return fallback;
    }

    const visibility = isRecord(stored.visibility) ? stored.visibility : {};
    const visible = defaults.fieldOrder.filter((id) => isVisible(id, visibility, defaults));

    return {
        fields: mergeOrder(stored.order, visible, defaults),
        density: isDensity(stored.density) ? stored.density : defaults.density,
        perPage: typeof stored.perPage === 'number' ? snapPerPage(stored.perPage, defaults.perPage) : defaults.perPage,
    };
}

/**
 * Whether the view shows the screen's default layout. Drives the "Reset view" button.
 *
 * @since TBD
 */
export function isDefaultLayout(view: ListTableView, defaults: ScreenDefaults): boolean {
    return Object.keys(toStoredLayout(view, defaults)).every((key) => key === 'version');
}
