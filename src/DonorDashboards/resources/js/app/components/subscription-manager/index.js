import FieldRow from '../field-row';
import Button from '../button';
import { Fragment, useState } from 'react';

const { __ } = wp.i18n;

import AmountControl from './amount-control';
import PaymentMethodControl from './payment-method-control';

import { updateSubscriptionWithAPI } from './utils';

const SubscriptionManager = ( { id, subscription } ) => {
	const [ amount, setAmount ] = useState( subscription.payment.amount.raw );
	const [ paymentMethod, setPaymentMethod ] = useState( null );
	const [ updating, setUpdating ] = useState( false );

	const handleUpdate = async() => {
		// Save with REST API
		setUpdating( true );
		await updateSubscriptionWithAPI( {
			id,
			amount,
			paymentMethod,
		} );
		setUpdating( false );
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
				gateway={ subscription.gateway.id }
				onChange={ ( val ) => setPaymentMethod( val ) }
			/>
			<FieldRow>
				<div>
					<Button icon="save" onClick={ () => handleUpdate() }>
						{ updating ? __( 'Updating', 'give' ) : __( 'Update', 'give' ) }
					</Button>
				</div>
			</FieldRow>
		</Fragment>
	);
};
export default SubscriptionManager;
