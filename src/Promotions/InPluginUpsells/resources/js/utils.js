/**
 * Replaces double asterisks with `<strong>` tags.
 *
 * @param {string} text
 * @returns {string}
 */
export function transformStrong(text) {
    // Keep track of whether we're inside a <strong> tag.
    let startingTag = false;

    return text.replaceAll('**', () => {
        // Reverse the starting tag state to determine the current state.
        startingTag = !startingTag;
        // Return the appropriate tag.
        return startingTag ? '<strong>' : '</strong>';
    });
}

/**
 * Replaces double asterisks with `<em>` tags.
 *
 * @param {string} text
 * @returns {string}
 */
export function transformEmphasis(text) {
    // Keep track of whether we're inside an <em> tag.
    let startingTag = false;

    return text.replaceAll('**', () => {
        // Reverse the starting tag state to determine the current state.
        startingTag = !startingTag;
        // Return the appropriate tag.
        return startingTag ? '<em>' : '</em>';
    });
}

/**
 * Get an asset URL.
 *
 * @param {string} filename (without leading slash).
 * @returns {string} asset URL
 */
export const assetUrl = (filename) => `${window.GiveAddons.assetsUrl}${filename}`;
