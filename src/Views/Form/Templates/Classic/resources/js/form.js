/** @jsx h */
/** @jsxFragment h */
import h from 'vhtml';
import {domIsReady, insertAfter, removeNode} from './not-jquery.js';

// Transforms document for classic template
function transform() {
	removeNode(document.querySelector('#give_error_test_mode')); // Get out of my way!

	insertAfter(
		document.querySelector('.give-personal-info-section'),
		document.querySelector('.give-donation-amount-section'),
	);

	document.querySelector('.give-payment-details-section').append(
		document.querySelector('#give_purchase_form_wrap')
	)

	document.querySelector('.give-personal-info-section legend:first-of-type').textContent = classicTemplateOptions.donor_information.headline;

	document.querySelectorAll('.give-donation-level-btn:not(.give-btn-level-custom)').forEach(node => {
		node.innerHTML = (
			<span class="give-formatted-currency">
				<span class="give-currency-symbol">$</span>
				<span>{node.getAttribute('value')}</span>
			</span>
		);
	});
}

domIsReady(transform);
