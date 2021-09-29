import { Fragment, useMemo, useRef, useState } from 'react';
import FieldRow from '../field-row';
import Button from '../button';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

import { __ } from '@wordpress/i18n';

import AmountControl from './amount-control';
import PaymentMethodControl from './payment-method-control';

import { updateSubscriptionWithAPI } from './utils';

import './style.scss';

const SubscriptionManager = ( { id, subscription } ) => {
	const gatewayRef = useRef();

	const [ amount, setAmount ] = useState( subscription.payment.amount.raw );
	const [ isUpdating, setIsUpdating ] = useState( false );
	const [ updated, setUpdated ] = useState( false );

	const amountOptions = useMemo(() => {
		const options = subscription.form.amounts.map(
			amount => ({
				value: amount.raw,
				label: amount.formatted,
			}),
		);

		if (subscription.form.custom_amount) {
			options.push({
				value: 'custom_amount',
				label: __('Custom Amount', 'give'),
			});
		}

		return options;

	}, [subscription]);

	const handleUpdate = async () => {
		if ( isUpdating ) {
			return;
		}

		setIsUpdating( true );

		const paymentMethod = await gatewayRef.current.getPaymentMethod();

		if ( 'error' in paymentMethod ) {
			setIsUpdating( false );
			return;
		}

		await updateSubscriptionWithAPI( {
			id,
			amount,
			paymentMethod
		} );

		setUpdated( true );
		setIsUpdating( false );
	};

	return (
		<Fragment>
			<AmountControl
				form={ subscription.form }
				payment={ subscription.payment }
				options={ amountOptions }
				onChange={ setAmount }
				value={ amount }
			/>
			<PaymentMethodControl
				forwardedRef={ gatewayRef }
				label={ __( 'Payment Method', 'give' ) }
				gateway={ subscription.gateway }
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
