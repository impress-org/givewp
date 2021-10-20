/** @jsx h */
import h from 'vhtml';
import {domIsReady} from './domIsReady';

import styles from './form.module.css';

/**
 * Creates a DOM Node from an HTML string.
 *
 * @param {string} htmlString
 * @returns {Node}
 */
function nodeFromString(htmlString) {
	const temp = document.createElement('template');
	htmlString.trim();
	temp.innerHTML = htmlString;
	return temp.content.firstChild;
}

/**
 * Get the named nodes for the given selectors, but only when needed.
 *
 * Lazy loads nodes and caches them for subsequent accesses.
 *
 * @param {object} map
 * @returns {Proxy}
 */
const gatherNodes = nodeMap => new Proxy(nodeMap, {
	cache: new Map(),
	get(target, prop) {
		if (!this.cache.has(prop)) this.cache.set(prop, document.querySelector(target[prop]));
		return this.cache.get(prop);
	},
	set(_target, prop, value) {
		this.cache.set(prop, value);
	},
});

/**
 * The missing after version of insertBefore.
 *
 * @param {Node} newNode
 * @param {Node} referenceNode
 */
function insertAfter(newNode, referenceNode) {
	referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
}


// TODO:
// - Get localized option data.
// - Why is there a second donation button?
// - ...
function transform() {
	// Collect some more semantic references to nodes up front. These will lazy
	// load so ideally do not destructure them.
	const nodes = gatherNodes({
		formWrap: '.give-form-wrap',
		title: '.give-form-title',
		personalInfo: '#give_checkout_user_info',
		personalInfoLegend: '#give_checkout_user_info > legend',
		paymentMethod: '#give-payment-mode-select',
		donationAmounts: '#give-donation-level-button-wrap',
	});

	nodes.formWrap.classList.add(styles.form);

	// TODO: replace with option data
	nodes.title.outerHTML = (
		<div className={styles.hero}>
			<h1>{nodes.title.textContent}</h1>
			<p>Hello, World!</p>
		</div>
	);

	// Move the personal info before the payment method.
	nodes.paymentMethod.parentNode.insertBefore(nodes.personalInfo, nodes.paymentMethod);

	// Change personal info
	nodes.personalInfoLegend.textContent = 'Who’s giving today?'; // TODO: replace with option data
	insertAfter(
		nodeFromString(<p>Hello, World!</p>), // TODO: replace with option data
		nodes.personalInfoLegend.nextSibling,
	);
}

domIsReady(transform);
