import { CardElement, useStripe, useElements } from '@stripe/react-stripe-js';
import { useState, useEffect } from 'react';
import './style.scss';

const CardControl = ( { onChange, value } ) => {
	const stripe = useStripe();
	const elements = useElements();

	const [ focused, setFocused ] = useState( false );
	const [ paymentMethodId, setPaymentMethodId ] = useState( value );

	useEffect( () => {
		if ( paymentMethodId ) {
			onChange( paymentMethodId );
		}
	}, [ paymentMethodId ] );

	const handleBlur = async() => {
		setFocused( false );

		if ( ! stripe || ! elements ) {
			// Stripe.js has not loaded yet. Make sure to disable
			// form submission until Stripe.js has loaded.
			return;
		}

		// Get a reference to a mounted CardElement. Elements knows how
		// to find your CardElement because there can only ever be one of
		// each type of element.
		const cardElement = elements.getElement( CardElement );

		// Use your card Element with other Stripe.js APIs
		const { error, paymentMethod } = await stripe.createPaymentMethod( {
			type: 'card',
			card: cardElement,
		} );

		if ( ! error ) {
			setPaymentMethodId( paymentMethod.id );
		}
	};

	return (
		<div className={ focused ? 'give-donor-profile-stripe-control give-donor-profile-stripe-control--focused' : 'give-donor-profile-stripe-control' }>
			<CardElement onFocus={ () => setFocused( true ) } onBlur={ () => handleBlur() } />
		</div>
	);
};
export default CardControl;
