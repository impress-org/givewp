/** @jsx h */
/** @jsxFrag h */
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
			<p className="give-personal-info-description">
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
			<p className="give-payment-details-description">
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
		const formattedAmount = node.getAttribute('value');
		const rawAmount = unFormatCurrency(formattedAmount, currency.decimalSeparator);

		const [symbolBefore, symbolAfter] = [currency.symbolPosition === 'before', currency.symbolPosition === 'after'];

		const CurrencySymbol = ({position}) => <span className={`give-currency-symbol-${position}`}>{currency.symbol}</span>;
		const AmountWithoutDecimals = () => <span className="give-amount-without-decimals">{window.accounting.format(rawAmount, 0, currency.thousandsSeparator)}</span>;
		const DecimalOfAmount = () => <span className="give-amount-decimal">{rawAmount.toFixed(currency.precision).split('.')[1]}</span>;
		const Amount = () => <span className="give-amount-formatted"><AmountWithoutDecimals /><DecimalOfAmount /></span>;

		node.setAttribute('aria-label', node.textContent);
		node.innerHTML = (
			<span className="give-formatted-currency" aria-hidden>
				{symbolBefore && <CurrencySymbol position="before" />}<Amount /> {symbolAfter && <CurrencySymbol position="after" />}
			</span>
		);
	});
}
