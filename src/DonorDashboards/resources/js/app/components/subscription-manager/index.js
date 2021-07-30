import FieldRow from '../field-row';
import Button from '../button';
import { Fragment, useState, useEffect } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

import { __ } from '@wordpress/i18n';

import AmountControl from './amount-control';
import PaymentMethodControl from './payment-method-control';

import { updateSubscriptionWithAPI } from './utils';

import './style.scss';

const SubscriptionManager = ( { id, subscription } ) => {
	const [ amount, setAmount ] = useState( subscription.payment.amount.raw );
	const [ paymentMethod, setPaymentMethod ] = useState( null );
	const [ isUpdating, setIsUpdating ] = useState( false );
	const [ updated, setUpdated ] = useState( true );
	const [ cardControl, setCardControl ] = useState( null );

	useEffect( () => {
		setUpdated( false );
	}, [ amount, paymentMethod ] );

	const handleUpdate = async() => {
		if ( isUpdating ) {
			return;
		}

		setIsUpdating( true );

		let paymentMethodStripe;

		if ( cardControl ) {
			const { stripe, cardElement } = cardControl;

			if ( ! cardElement._empty && ! cardElement._invalid ) {
				const { error, paymentMethod:method } = await stripe.createPaymentMethod( {
					type: 'card',
					card: cardElement,
				} );

				if ( ! error ) {
					paymentMethodStripe = {
						give_stripe_payment_method: method.id,
					}
				} else {
					setIsUpdating( false );
					return cardElement.focus();
				}
			} else {
				// Prevent user from updating the subscription if he entered invalid card details
				if ( cardElement._invalid ) {
					setIsUpdating( false );
					return cardElement.focus();
				}
			}
		}

		await updateSubscriptionWithAPI( {
			id,
			amount,
			paymentMethod: paymentMethodStripe ?? paymentMethod
		} );

		setUpdated( true );
		setIsUpdating( false );
	};

	return (
		<Fragment>
			<AmountControl
				form={ subscription.form }
				payment={ subscription.payment }
				onChange={ ( val ) => setAmount( val ) } value={ amount }
			/>
			<PaymentMethodControl
				label={ __( 'Payment Method', 'give' ) }
				gateway={ subscription.gateway }
				onFocus={ setCardControl }
				onChange={ ( val ) => setPaymentMethod( val ) }
			/>
			<FieldRow>
				<div>
					<Button onClick={ () => handleUpdate() }>
						{ updated ? (
							<Fragment>
								{ __( 'Updated', 'give' ) } <FontAwesomeIcon icon="check" fixedWidth />
							</Fragment>
						) : (
							<Fragment>
								{ __( 'Update Subscription', 'give' ) } <FontAwesomeIcon className={ isUpdating ? 'give-donor-dashboard__subscription-manager-spinner' : '' } icon={ isUpdating ? 'spinner' : 'save' } fixedWidth />
							</Fragment>
						) }
					</Button>
				</div>
			</FieldRow>
		</Fragment>
	);
};
export default SubscriptionManager;
