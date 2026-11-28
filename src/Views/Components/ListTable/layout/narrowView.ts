import type {View, ViewGrid} from '@wordpress/dataviews';
import type {Column} from '../ListTablePage/types';
import type {ListTableFieldRoles} from '../state/types';

/**
 * The card fields of a screen, checked against the columns the list really has. A role whose column is
 * missing is dropped (the Campaign column on a campaign's Forms tab, for example), and a field never
 * shows twice. Without roles, the column kinds pick them: the title field first, the first money column
 * under it, then the first status and the first date column.
 *
 * @since TBD
 */
export function resolveFieldRoles(
    roles: ListTableFieldRoles | undefined,
    columns: Column[],
    titleField?: string
): ListTableFieldRoles {
    const ids = columns.map((column) => column.id);
    const has = (id: string | undefined) => id !== undefined && ids.includes(id);
    const firstOfKind = (kind: Column['kind']) => columns.find((column) => column.kind === kind)?.id;

    const primary = roles && has(roles.primary) ? roles.primary : titleField;
    const description = roles ? roles.description : firstOfKind('money');
    const secondary = roles ? roles.secondary : [firstOfKind('status'), firstOfKind('date')];

    const resolvedDescription = has(description) && description !== primary ? description : undefined;

    return {
        primary,
        description: resolvedDescription,
        secondary: secondary.filter(
            (id, index) => has(id) && id !== primary && id !== resolvedDescription && secondary.indexOf(id) === index
        ),
    };
}

/**
 * The view DataViews renders in the narrow layout: one card per record, built from the field roles,
 * with the ID as a badge. Trailing fields (the old row actions column) come after the secondary fields.
 * Page, sort, filters and search stay as they are in the wide view.
 *
 * @since TBD
 */
export function toNarrowView(
    view: View,
    roles: ListTableFieldRoles,
    hasIdColumn: boolean,
    trailingFields: string[] = []
): ViewGrid {
    const idField = hasIdColumn ? ['id'] : [];

    return {
        ...(view as Omit<View, 'type' | 'layout'>),
        type: 'grid',
        titleField: roles.primary,
        descriptionField: roles.description,
        mediaField: undefined,
        showMedia: false,
        showTitle: true,
        showDescription: true,
        fields: [...roles.secondary, ...trailingFields, ...idField],
        layout: {badgeFields: idField},
    };
}

/**
 * Maps a change made in the narrow layout back onto the wide view. Only the query (page, page size,
 * sort, filters, search) comes from the narrow view; the columns, the title and the density stay as
 * the wide view has them, so the card layout is never kept or stored.
 *
 * @since TBD
 */
export function fromNarrowView<V extends View>(next: View, wide: V): V {
    return {
        ...wide,
        page: next.page,
        perPage: next.perPage,
        sort: next.sort,
        filters: next.filters,
        search: next.search,
    };
}
