import StripeControl from './stripe-control';
import PaypalControl from './paypal-control';
import AuthorizeControl from './authorize-control';

import { useEffect, useState } from 'react';

import './style.scss';

const PaymentMethodControl = ( { gateway, onChange, value } ) => {
	const [ paymentMethodControl, setPaymentMethodControl ] = useState( null );

	useEffect( () => {
		switch ( gateway ) {
			case 'stripe': {
				setPaymentMethodControl( <StripeControl onChange={ ( val ) => onChange( val ) } value={ value } /> );
				break;
			}
			case 'paypal': {
				setPaymentMethodControl( <PaypalControl /> );
				break;
			}
			case 'authorize': {
				setPaymentMethodControl( <AuthorizeControl /> );
				break;
			}
		}
	}, [] );

	return (
		<div className="give-donor-profile-payment-method-control">
			<label className="give-donor-profile-payment-method-control__label">Payment Method</label>
			{ paymentMethodControl }
		</div>
	);
};

export default PaymentMethodControl;
