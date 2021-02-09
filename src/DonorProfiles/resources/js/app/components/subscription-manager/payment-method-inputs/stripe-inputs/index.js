import { CardElement, useStripe, useElements } from '@stripe/react-stripe-js';
import { useState, useEffect } from 'react';
import './style.scss';

const StripeInputs = ( { onChange } ) => {
	const stripe = useStripe();
	const elements = useElements();

	const [ focused, setFocused ] = useState( false );
	const [ paymentMethodId, setPaymentMethodId ] = useState( null );

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
		<div className="give-donor-profile-stripe-inputs">
			<label className="give-donor-profile-stripe-inputs__label">Payment Method</label>
			<div className={ focused ? 'give-donor-profile-stripe-inputs__card give-donor-profile-stripe-inputs__card--focused' : 'give-donor-profile-stripe-inputs__card' }>
				<CardElement onFocus={ () => setFocused( true ) } onBlur={ () => handleBlur() } />
			</div>
		</div>
	);
};
export default StripeInputs;
