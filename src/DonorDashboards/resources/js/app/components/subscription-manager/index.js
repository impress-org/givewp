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

	useEffect( () => {
		setUpdated( false );
	}, [ amount, paymentMethod ] );

	const handleUpdate = async() => {
		// Save with REST API
		setIsUpdating( true );
		await updateSubscriptionWithAPI( {
			id,
			amount,
			paymentMethod,
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
