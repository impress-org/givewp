/**
 * The first sentence of a search hint. The hint is one long server string; the line under the box
 * shows only this, and the full text goes in the help popover.
 *
 * @since TBD
 */
export function shortHint(hint: string): string {
    const text = hint.trim();

    return text.split(/(?<=[.!?])\s+/)[0] ?? text;
}
