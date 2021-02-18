import FieldRow from '../field-row';
import Button from '../button';
import { Fragment, useState } from 'react';

const { __ } = wp.i18n;

import AmountControl from './amount-control';
import PaymentMethodControl from './payment-method-control';

import { saveSubscriptionWithAPI } from './utils';

const SubscriptionManager = ( { id, subscription } ) => {
	const [ amount, setAmount ] = useState( subscription.payment.amount.raw );
	const [ paymentMethod, setPaymentMethod ] = useState( null );
	const [ saving, setSaving ] = useState( false );

	const handleSave = async() => {
		// Save with REST API
		setSaving( true );
		await saveSubscriptionWithAPI( {
			id,
			amount,
			paymentMethod,
		} );
		setSaving( false );
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
					<Button icon="save" onClick={ () => handleSave() }>
						{ saving ? __( 'Saving', 'give' ) : __( 'Save', 'give' ) }
					</Button>
				</div>
			</FieldRow>
		</Fragment>
	);
};
export default SubscriptionManager;
