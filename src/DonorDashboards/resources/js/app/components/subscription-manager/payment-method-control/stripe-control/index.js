import { Elements } from '@stripe/react-stripe-js';
import { loadStripe } from '@stripe/stripe-js';
import { useState, useEffect } from 'react';

import StripeCardControl from './stripe-card-control';

const StripeControl = ( { label, onChange, value } ) => {
	const [ stripePromise, setStripePromise ] = useState( null );

	useEffect( () => {
		const stripeKey = window.give_stripe_vars.publishable_key;
		if ( stripeKey ) {
			setStripePromise( loadStripe( stripeKey ) );
		}
	}, [] );

	const fonts = [ {
		src: 'url(https://fonts.googleapis.com/css2?family=Montserrat:wght@500)',
		family: 'Montserrat',
	} ];

	return (
		<Elements stripe={ stripePromise } fonts={ fonts }>
			<StripeCardControl label={ label } onChange={ ( val ) => onChange( val ) } value={ value } />
		</Elements>
	);
};

export default StripeControl;
