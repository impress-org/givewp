/**
 * The fields with `id` added before the first field that comes after it in the default order. A field
 * the default order does not know, or one with nothing after it, goes last.
 *
 * @since TBD
 */
export function insertAtDefaultPosition(fields: string[], id: string, fieldOrder: string[]): string[] {
    const rankOf = (field: string) => fieldOrder.indexOf(field);
    const rank = rankOf(id);
    const at = rank === -1 ? -1 : fields.findIndex((field) => rankOf(field) > rank);

    return at === -1 ? [...fields, id] : [...fields.slice(0, at), id, ...fields.slice(at)];
}

/**
 * The fields with the pinned field (the legacy row actions) moved last. The same list comes back when
 * there is no pinned field, it is not shown, or it is already last.
 *
 * @since TBD
 */
export function withPinnedLast(fields: string[], pinned: string | undefined): string[] {
    if (pinned === undefined || !fields.includes(pinned) || fields[fields.length - 1] === pinned) {
        return fields;
    }

    return [...fields.filter((id) => id !== pinned), pinned];
}
