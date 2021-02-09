import FieldRow from '../field-row';
import Button from '../button';
import { Fragment, useState } from 'react';

import AmountInputs from './amount-inputs';
import PaymentMethodInputs from './payment-method-inputs';

const SubscriptionManager = ( { subscription } ) => {
	const [ paymentMethod, setPaymentMethod ] = useState( null );

	const handleSave = () => {
		// Save with REST API
	};

	return (
		<Fragment>
			<AmountInputs form={ subscription.form } payment={ subscription.payment } />
			<PaymentMethodInputs gateway={ subscription.gateway.id } onChange={ ( val ) => setPaymentMethod( val ) } value={ paymentMethod } />
			<FieldRow>
				<div>
					<Button icon="save" onClick={ () => handleSave() }>
						Save
					</Button>
				</div>
			</FieldRow>
		</Fragment>
	);
};
export default SubscriptionManager;
