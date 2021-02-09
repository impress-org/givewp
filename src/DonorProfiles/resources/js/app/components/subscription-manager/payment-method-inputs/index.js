import StripeInputs from './stripe-inputs';
import PaypalInputs from './paypal-inputs';
import AuthorizeInputs from './authorize-inputs';

import { useEffect, useState } from 'react';

import { Elements } from '@stripe/react-stripe-js';
import { loadStripe } from '@stripe/stripe-js';

const PaymentMethodInputs = ( { gateway, onChange, value } ) => {
	const [ stripePromise, setStripePromise ] = useState( null );

	useEffect( () => {
		if ( gateway === 'stripe' && stripePromise === null ) {
			const stripeKey = window.give_stripe_vars.publishable_key;
			if ( stripeKey ) {
				setStripePromise( loadStripe( stripeKey ) );
			}
		}
	}, [ gateway, stripePromise ] );

	switch ( gateway ) {
		case 'stripe': {
			return <Elements stripe={ stripePromise }>
				<StripeInputs onChange={ ( val ) => onChange( val ) } value={ value } />
			</Elements>; }
		case 'paypal': {
			return <PaypalInputs />;
		}
		case 'authorize': {
			return <AuthorizeInputs />;
		}
	}
};

export default PaymentMethodInputs;
