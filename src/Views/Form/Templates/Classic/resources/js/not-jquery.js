/**
 * Collect all siblings until a selector.
 *
 * @param {Node} node
 * @param {string} selector
 *
 * @return {Node[]}
 */
export function nextUntil(node, selector) {
	const siblings = [];
	const sibling = node.nextElementSibling;

	while (sibling) {
		if (sibling.matches(selector)) break;

		siblings.push(sibling);
		sibling = sibling.nextElementSibling;
	}

	return siblings;
}

/**
 * The missing after version of insertBefore.
 *
 * @param {Node} newNode
 * @param {Node} referenceNode
 */
export function insertAfter(newNode, referenceNode) {
	referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
}

/**
 * @param {Node} node
 */
export function removeNode(nodeToRemove) {
	nodeToRemove.parentNode.removeChild(nodeToRemove);
}

/**
 * Creates a DOM Node from an HTML string.
 *
 * @param {string} htmlString
 * @returns {Node}
 */
export function nodeFromString(htmlString) {
	const temp = document.createElement('template');
	htmlString.trim();
	temp.innerHTML = htmlString;
	return temp.content.firstChild;
}

/**
 * Execute a function when the DOM is fully loaded.
 *
 * @param {function} fn
 */
export const domIsReady = fn => document.readyState !== 'loading'
	? window.setTimeout(fn, 0)
	: document.addEventListener('DOMContentLoaded', fn);
