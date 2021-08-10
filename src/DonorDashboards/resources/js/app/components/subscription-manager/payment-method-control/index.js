import StripeControl from './stripe-control';
import CardControl from './card-control';

import './style.scss';

const PaymentMethodControl = ( { gateway, label, forwardedRef } ) => {
	switch ( gateway.id ) {
		case 'stripe':
		case 'stripe_apple_pay':
		case 'stripe_becs':
		case 'stripe_checkout':
		case 'stripe_google_pay': {
			return <StripeControl
				forwardedRef={ forwardedRef }
				label={ label }
				gateway={gateway}
			/>;
		}
		case 'authorize':
		case 'paypalpro': {
			return <CardControl
				forwardedRef={ forwardedRef }
				label={ label }
				gateway={gateway}
			/>;
		}
		default: {
			return null;
		}
	}
};

export default PaymentMethodControl;
