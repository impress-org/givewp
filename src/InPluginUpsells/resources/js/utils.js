/**
 * @param {string} text
 */
export function transformStrong(text) {
    let startingTag = false;

    return text.replaceAll('**', () => {
        startingTag = !startingTag;
        return startingTag ? '<strong>' : '</strong>';
    });
}
