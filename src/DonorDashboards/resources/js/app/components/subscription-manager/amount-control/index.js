import { useEffect, useState } from 'react';

import CurrencyControl from '../../currency-control';
import FieldRow from '../../field-row';
import SelectControl from '../../select-control';

import { __ } from '@wordpress/i18n';

const CUSTOM_AMOUNT = 'custom_amount';

/**
 * This control provides preset options however it allows the user to specify a
 * custom option.
 */
const AmountControl = ( { currency, onChange, value, options, min, max } ) => {
	// The select value acts as a proxy for the actual value.
	const [selectValue, setSelectValue] = useState(
		// Determine whether the value is one of the predefined values and set
		// the select input’s initial value accordingly.
		() => options.map(option => option.value).includes(value) ? value : CUSTOM_AMOUNT,
	);
	// We only call the onChange if the value is one of the predefined values.
	// Otherwise, we effectively delegate control to the currency input.
	useEffect(() => {
		if (selectValue !== CUSTOM_AMOUNT) {
			onChange(selectValue);
		}
	}, [selectValue, onChange]);

	const [validationError, setValidationError] = useState();
	const clearValidationError = () => setValidationError(null);

	return (
		<div className="give-donor-dashboard-amount-inputs">
			<FieldRow>
				<div>
					<SelectControl
						label="Subscription Amount"
						options={ options }
						value={ selectValue }
						onChange={ setSelectValue }
					/>
				</div>
				<div>
					{ selectValue === CUSTOM_AMOUNT && (
						<CurrencyControl
							label={ __( 'Custom Amount', 'give' ) }
							currency={ currency }
							min={ min }
							max={ max }
							value={ value }
							onChange={ onChange }
							setValidationError={ setValidationError }
							clearValidationError={ clearValidationError }
						/>
					) }
				</div>
			</FieldRow>
			{validationError && (
				<FieldRow>
					<p>{validationError}</p>
				</FieldRow>
			)}
		</div>
	);
};

export default AmountControl;
