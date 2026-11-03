import apiFetch from '@wordpress/api-fetch';
import type {ListTableScreenKey, StoredLayout} from './types';

/**
 * What the page prints before the list table script: the route path and the signed-in user's own
 * stored layouts, one per screen the user can open.
 *
 * @since TBD
 */
export interface GiveListTableLayouts {
    path: string;
    layouts: Partial<Record<ListTableScreenKey, StoredLayout>>;
}

declare global {
    interface Window {
        GiveListTableLayouts?: GiveListTableLayouts;
    }
}

const DEFAULT_PATH = '/givewp/v3/list-table-layouts';

/**
 * @since TBD
 */
export type LayoutRequestOptions = {
    /** Lets the request finish after the page is gone (sent on `pagehide`). */
    keepalive?: boolean;
};

const routeOf = (screenKey: ListTableScreenKey) => `${window.GiveListTableLayouts?.path ?? DEFAULT_PATH}/${screenKey}`;

/**
 * The stored layout of a screen, as the page printed it. It is read as it is: `applyStoredLayout`
 * checks its shape.
 *
 * @since TBD
 */
export function readStoredLayout(screenKey: ListTableScreenKey): unknown {
    return window.GiveListTableLayouts?.layouts?.[screenKey];
}

/**
 * Replaces the signed-in user's stored layout of a screen.
 *
 * @since TBD
 */
export function saveLayout(
    screenKey: ListTableScreenKey,
    layout: StoredLayout,
    {keepalive = false}: LayoutRequestOptions = {}
): Promise<unknown> {
    return apiFetch({path: routeOf(screenKey), method: 'PUT', data: layout, keepalive});
}

/**
 * Removes the signed-in user's stored layout of a screen, so the screen defaults apply again.
 *
 * @since TBD
 */
export function clearLayout(
    screenKey: ListTableScreenKey,
    {keepalive = false}: LayoutRequestOptions = {}
): Promise<unknown> {
    return apiFetch({path: routeOf(screenKey), method: 'DELETE', keepalive});
}
