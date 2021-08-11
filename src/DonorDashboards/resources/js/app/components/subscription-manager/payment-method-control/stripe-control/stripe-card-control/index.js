import { CardElement, useStripe } from '@stripe/react-stripe-js';
import { useState, useImperativeHandle } from 'react';
import './style.scss';

const CardControl = ( { label, forwardedRef } ) => {
	const stripe = useStripe();
	const [ cardInput, setCardInput ] = useState( null );
	const [ focused, setFocused ] = useState( false );

	useImperativeHandle( forwardedRef, () => ( {
		async getPaymentMethod() {
			if ( ! cardInput._empty && ! cardInput._invalid ) {
				const { error, paymentMethod } = await stripe.createPaymentMethod( {
					type: 'card',
					card: cardInput,
				} );

				if ( ! error ) {
					return {
						give_stripe_payment_method: paymentMethod.id,
					}
				}

				cardInput.focus();
				return {
					error: true
				}
			}

			// Prevent user from updating the subscription if he entered invalid card details
			if ( cardInput._invalid ) {
				cardInput.focus();
				return {
					error: true
				}
			}

			return {
				give_stripe_payment_method: null
			}
		},
	} ), [ cardInput ] );

	const handleCardInputEvents = ( cardInputElement ) => {
		setCardInput( cardInputElement );

		cardInputElement.on( 'focus', function() {
			setFocused( true );
		} );

		cardInputElement.on( 'blur', function() {
			setFocused( false );
		} );

		cardInputElement.on( 'change', function( { empty } ) {
			if ( empty ) {
				cardInputElement.clear();
			}
		} );
	};

	return (
		<div className="give-donor-dashboard-stripe-card-control">
			<label className="give-donor-dashboard-stripe-card-control__label">{ label }</label>
			<div className={ focused ? 'give-donor-dashboard-stripe-card-control__input give-donor-dashboard-stripe-card-control__input--focused' : 'give-donor-dashboard-stripe-card-control__input' }>
				<CardElement
					style={ { base: { fontFamily: 'Montserrat' } } }
					onReady={ handleCardInputEvents }
				/>
			</div>
		</div>
	);
};
export default CardControl;
