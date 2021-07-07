import StripeControl from './stripe-control';
import CardControl from './card-control';

import './style.scss';

const PaymentMethodControl = ( { gateway, label, onChange } ) => {
	switch ( gateway ) {
		case 'stripe':
		case 'stripe_apple_pay':
		case 'stripe_becs':
		case 'stripe_checkout':
		case 'stripe_google_pay': {
			return <StripeControl label={ label } onChange={ ( val ) => onChange( val ) } />;
		}
		case 'authorize':
		case 'paypalpro': {
			return <CardControl label={ label } onChange={ ( val ) => onChange( val ) } />;
		}
		default: {
			return null;
		}
	}
};

export default PaymentMethodControl;
