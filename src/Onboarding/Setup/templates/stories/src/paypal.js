import icon from '../../../../../../../../assets/dist/images/setup-page/paypal@2x.min.png';
import button from '../../../../../../../../assets/dist/images/setup-page/paypal.svg';
import template from '../../row-item.html';

export default () => {
	return template
		.replace( /{{\s*icon\s*}}/gi, icon )
		.replace( /{{\s*title\s*}}/gi, 'Connect to Paypal' )
		.replace( /{{\s*description\s*}}/gi,
			'PayPal is synonymous with nonprofits and online charitable gifts. It’s been the go-to payment merchant in for many of the worlds top NGOs. Accept PayPal, Credit and Debit Cards, and more using PayPal’s Smart Buttons without any added platform fees.'
		)
		.replace( /{{\s*action\s*}}/gi, '<img src="' + button + '" alt="Connect to PayPal" />' );
};
