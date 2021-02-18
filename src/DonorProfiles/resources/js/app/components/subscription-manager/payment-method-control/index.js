import StripeControl from './stripe-control';
import CardControl from './card-control';

import './style.scss';

const PaymentMethodControl = ( { gateway, label, onChange } ) => {
	switch ( gateway ) {
		case 'stripe': {
			return <StripeControl label={ label } onChange={ ( val ) => onChange( val ) } />;
		}
		default: {
			return <CardControl label={ label } onChange={ ( val ) => onChange( val ) } />;
		}
	}
};

export default PaymentMethodControl;
