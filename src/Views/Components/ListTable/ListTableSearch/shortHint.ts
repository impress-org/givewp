/**
 * The first sentence of a search hint. The hint is one long server string; the line under the box
 * shows only this, and the full text goes in the help tooltip.
 *
 * @since TBD
 */
export function shortHint(hint: string): string {
    return hint.split(/(?<=[.!?])\s+/)[0] ?? hint;
}
