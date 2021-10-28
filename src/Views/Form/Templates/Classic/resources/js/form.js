/** @jsx h */
/** @jsxFragment h */
import h from 'vhtml';
import {domIsReady, insertAfter, nodeFromString, removeNode} from './not-jquery.js';

// Transforms document for classic template
domIsReady(() => {
	removeTestModeMessage();
	movePersonalInfoSectionAfterDonationAmountSection();
	movePaymentFormInsidePaymentDetailsSection();
	setPersonalInfoTitle();
	addPersonalInfoDescription();
	setPaymentDetailsTitle();
	addPaymentDetailsDescription();
	splitDonationLevelAmountsIntoParts();
});

/**
 * Individual transformations
 */

function removeTestModeMessage() {
	removeNode(document.querySelector('#give_error_test_mode')); // Get out of my way!
}

function movePersonalInfoSectionAfterDonationAmountSection() {
	insertAfter(
		document.querySelector('.give-personal-info-section'),
		document.querySelector('.give-donation-amount-section'),
	);
}

function setPersonalInfoTitle() {
	document.querySelector('.give-personal-info-section legend:first-of-type').textContent = classicTemplateOptions.donor_information.headline;
}

function addPersonalInfoDescription() {
	insertAfter(
		nodeFromString(
			<p class="give-personal-info-description">
				{classicTemplateOptions.donor_information.description}
			</p>
		),
		document.querySelector('.give-personal-info-section legend:first-of-type'),
	);
}

function setPaymentDetailsTitle() {
	document.querySelector('.give-payment-mode-label').textContent = classicTemplateOptions.payment_method.headline;
}

function addPaymentDetailsDescription() {
	insertAfter(
		nodeFromString(
			<p class="give-payment-details-description">
				{classicTemplateOptions.payment_method.description}
			</p>
		),
		document.querySelector('.give-payment-mode-label'),
	);
}

function movePaymentFormInsidePaymentDetailsSection() {
	document.querySelector('.give-payment-details-section').append(
		document.querySelector('#give_purchase_form_wrap')
	);
}

function splitDonationLevelAmountsIntoParts() {
	const unFormatCurrency = (...args) => window.Give.fn.unFormatCurrency(...args);
	const getGlobalVar = (...args) => window.Give.fn.getGlobalVar(...args);
	const currency = {
		code: getGlobalVar('currency'),
		decimalSeparator: getGlobalVar('decimal_separator'),
		precision: Number.parseInt(getGlobalVar('number_decimals')),
		symbol: getGlobalVar('currency_sign'),
		symbolPosition: getGlobalVar('currency_pos'),
		thousandsSeparator: getGlobalVar('thousands_separator'),
	};

	document.querySelectorAll('.give-donation-level-btn:not(.give-btn-level-custom)').forEach(node => {
		const CurrencySymbol = () => <span class="give-currency-symbol">{currency.symbol}</span>;

		const rawAmount = unFormatCurrency(node.getAttribute('value'), currency.decimalSeparator);
		const decimalOfAmount = rawAmount.toFixed(currency.precision).split('.')[1];
		const amountWithoutDecimals = window.accounting.format(rawAmount, 0, currency.thousandsSeparator);

		node.innerHTML = (
			<span class="give-formatted-currency">
				{currency.symbolPosition === 'before' && <CurrencySymbol />}
				<span class="amount">
					<span>{amountWithoutDecimals}</span><span>{currency.decimalSeparator}{decimalOfAmount}</span>
				</span>
				{currency.symbolPosition === 'after' && <CurrencySymbol />}
			</span>
		);
	});
}
