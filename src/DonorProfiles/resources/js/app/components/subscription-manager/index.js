import FieldRow from '../field-row';
import Button from '../button';
import { Fragment } from 'react';

import AmountInputs from './amount-inputs';
import PaymentMethodInputs from './payment-method-inputs';

const SubscriptionManager = ( { subscription } ) => {
	return (
		<Fragment>
			<AmountInputs form={ subscription.form } payment={ subscription.payment } />
			<PaymentMethodInputs gateway={ subscription.gateway.id } />
			<FieldRow>
				<div>
					<Button icon="save">
						Save
					</Button>
				</div>
			</FieldRow>
		</Fragment>
	);
};
export default SubscriptionManager;
