import {domIsReady, insertAfter, removeNode} from './not-jquery.js';

// Transforms document for classic template
function transform() {
	removeNode(document.querySelector('#give_error_test_mode')); // Get out of my way!

	insertAfter(
		document.querySelector('#give-personal-info-fields'),
		document.querySelector('#give-donation-level-fields'),
	);

	document.querySelector('#give-payment-method-fields').append(
		document.querySelector('#give_purchase_form_wrap')
	)
}

domIsReady(transform);
