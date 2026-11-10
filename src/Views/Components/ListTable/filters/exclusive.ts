/**
 * The new selection of a list with exclusive values (Trash, "No campaign"). An exclusive value that
 * was just added stays alone. Any other value that is added drops an exclusive one.
 *
 * @since TBD
 */
export function applyExclusiveValues(previous: string[], next: string[], exclusive: string[]): string[] {
    const addedExclusive = next.find((value) => exclusive.includes(value) && !previous.includes(value));

    if (addedExclusive !== undefined) {
        return [addedExclusive];
    }

    if (next.length > 1) {
        return next.filter((value) => !exclusive.includes(value));
    }

    return next;
}
