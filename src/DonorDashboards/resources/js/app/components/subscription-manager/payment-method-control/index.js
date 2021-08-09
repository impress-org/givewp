import StripeControl from './stripe-control';
import CardControl from './card-control';

import './style.scss';

const PaymentMethodControl = ( { gateway, label, onChange } ) => {
	switch ( gateway.id ) {
		case 'stripe':
		case 'stripe_apple_pay':
		case 'stripe_becs':
		case 'stripe_checkout':
		case 'stripe_google_pay': {
			return <StripeControl
				label={ label }
				gateway={gateway}
				onChange={ ( val ) => onChange( val ) }
			/>;
		}
		case 'authorize':
		case 'paypalpro': {
			return <CardControl
				label={ label }
				gateway={gateway}
				onChange={ ( val ) => onChange( val ) }
			/>;
		}
		default: {
			return null;
		}
	}
};

export default PaymentMethodControl;
