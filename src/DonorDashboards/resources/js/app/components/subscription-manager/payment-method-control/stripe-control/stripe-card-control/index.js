import { CardElement, useStripe } from '@stripe/react-stripe-js';
import { useState } from 'react';
import './style.scss';

const CardControl = ( { label, onFocus } ) => {
	const stripe = useStripe();
	const [ focused, setFocused ] = useState( false );

	const handleCardInputEvents = ( cardInputElement ) => {

		cardInputElement.on( 'focus', function(){
			setFocused( true );

			if ( typeof onFocus === 'function' ) {
				onFocus( {
					stripe,
					cardElement: cardInputElement
				} );
			}
		} );

		cardInputElement.on( 'blur', function() {
			setFocused( false );
		} );

		cardInputElement.on( 'change', function( { empty } ) {
			if ( empty ) {
				cardInputElement.clear();
			}
		} );
	}

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
