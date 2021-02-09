import { Elements } from '@stripe/react-stripe-js';
import { loadStripe } from '@stripe/stripe-js';
import { useState, useEffect } from 'react';

import CardControl from './card-control';

const StripeControl = ( { onChange, value } ) => {
	const [ stripePromise, setStripePromise ] = useState( null );

	useEffect( () => {
		const stripeKey = window.give_stripe_vars.publishable_key;
		if ( stripeKey ) {
			setStripePromise( loadStripe( stripeKey ) );
		}
	}, [] );

	return (
		<Elements stripe={ stripePromise }>
			<CardControl onChange={ ( val ) => onChange( val ) } value={ value } />
		</Elements>
	);
};

export default StripeControl;
