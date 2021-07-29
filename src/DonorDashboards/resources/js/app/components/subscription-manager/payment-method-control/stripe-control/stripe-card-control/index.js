import { CardElement, useStripe } from '@stripe/react-stripe-js';
import { useState, useEffect } from 'react';
import './style.scss';

const CardControl = ( { label, onChange, onReady, value } ) => {
	const stripe = useStripe();

	const [ focused, setFocused ] = useState( false );
	const [ paymentMethodId, setPaymentMethodId ] = useState( value );

	useEffect( () => {
		if ( paymentMethodId ) {
			handleReady( true );
			onChange( {
				give_stripe_payment_method: paymentMethodId,
			} );
		}
	}, [ paymentMethodId ] );

	const handleReady = ( ready ) => {
		if ( typeof onReady === 'function' ) {
			onReady( ready );
		}
	}

	const handleCard = ( cardElement ) => {

		cardElement.on( 'focus', function(){
			setFocused( true );
		} );

		cardElement.on( 'blur', async function() {
			setFocused( false );

			if ( cardElement._empty || cardElement._invalid ) {
				return;
			}

			const { error, paymentMethod } = await stripe.createPaymentMethod( {
				type: 'card',
				card: cardElement,
			} );

			if ( ! error ) {
				setPaymentMethodId( paymentMethod.id );
			}
		} );

		cardElement.on( 'change', function( { empty } ) {
			handleReady( empty );

			if ( empty ) {
				cardElement.clear();
			}
		} );
	}

	return (
		<div className="give-donor-dashboard-stripe-card-control">
			<label className="give-donor-dashboard-stripe-card-control__label">{ label }</label>
			<div className={ focused ? 'give-donor-dashboard-stripe-card-control__input give-donor-dashboard-stripe-card-control__input--focused' : 'give-donor-dashboard-stripe-card-control__input' }>
				<CardElement
					style={ { base: { fontFamily: 'Montserrat' } } }
					onReady={ handleCard }
				/>
			</div>
		</div>
	);
};
export default CardControl;
