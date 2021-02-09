import TextControl from '../../text-control';
import FieldRow from '../../field-row';
import SelectControl from '../../select-control';
import { useState } from 'react';

const AmountInputs = ( { form } ) => {
	const amountOptions = form.amounts.map( ( amount ) => {
		return {
			value: amount._give_amount,
			label: amount._give_amount,
		};
	} );

	if ( form.custom_amount ) {
		amountOptions.push( {
			value: 'custom_amount',
			label: 'Custom Amount',
		} );
	}

	const [ selectedAmount, setSelectedAmount ] = useState( null );

	return (
		<div className="give-donor-profile-amount-inputs">
			<FieldRow>
				<div>
					<SelectControl label="Subscription Amount" options={ amountOptions } value={ selectedAmount } onChange={ ( value ) => setSelectedAmount( value ) } />
				</div>
				<div>
					{ selectedAmount === 'custom_amount' && (
						<TextControl label="Custom Amount" />
					) }
				</div>
			</FieldRow>
		</div>
	);
};

export default AmountInputs;
