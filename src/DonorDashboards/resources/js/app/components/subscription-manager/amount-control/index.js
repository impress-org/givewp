import { useCallback, useEffect, useState } from 'react';
import CurrencyInput, { formatValue } from 'react-currency-input-field';
import { __, sprintf } from '@wordpress/i18n';

import { toUniqueId } from '../../../utils';
import FieldRow from '../../field-row';
import SelectControl from '../../select-control';

import './style.scss';

const CUSTOM_AMOUNT = 'custom_amount';

/**
 * Converts a float to minor currency.
 *
 * @param {string} float
 * @param {number} precision
 * @return {number}
 */
const minorOfFloat = ( float, precision ) => Number.parseFloat( float ) * Math.pow( 10, precision );

/**
* This control provides preset options however it allows the user to specify a
* custom option.
*
* Heads up, you probably want to take a good look at what’s happening before
* using this elsewhere.
*/
const AmountControl = ( { currency, onChange, value, options, min, max } ) => {
	// This is the configuration for the CurrencyInput as well as formatting
	// values in validation errors
	const formatConfig = {
		decimalScale: currency.numberDecimals,
		decimalsLimit: currency.numberDecimals,
		prefix: currency.currencyPosition === 'before' ? currency.symbol : null,
		suffix: currency.currencyPosition === 'after' ? currency.symbol : null,
		decimalSeparator: currency.decimalSeparator,
		groupSeparator: currency.thousandsSeparator,
	};

	// The select value acts as a proxy for the actual value.
	const [ selectValue, setSelectValue ] = useState(
		// Determine whether the value is one of the predefined values and set
		// the select input’s initial value accordingly.
		() => options.map( ( option ) => option.value ).includes( value ) ? value : CUSTOM_AMOUNT,
	);
	// We only call the onChange if the value is one of the predefined values.
	// Otherwise, we effectively delegate control to the currency input.
	useEffect( () => {
		if ( selectValue !== CUSTOM_AMOUNT ) {
			onChange( selectValue );
		}
	}, [ selectValue, onChange ] );

	const [validationError, setValidationError] = useState();
	// Ideally, we’d just use the value from the event.target, however, that’s
	// formatted all nicely and we want a float, so we can just use the
	const validateCustomAmount = useCallback( () => {
		if ( value ) {
			const minorOfValue = minorOfFloat( value, currency.numberDecimals );
			const minorOfMin = minorOfFloat( min, currency.numberDecimals );
			const minorOfMax = minorOfFloat( max, currency.numberDecimals );

			if ( minorOfValue > minorOfMax ) {
				setValidationError(
					sprintf(
						__( 'Amount must be less than %s', 'give' ),
						formatValue( { value: max, ...formatConfig } ),
					),
				);
			} else if ( minorOfValue < minorOfMin ) {
				setValidationError(
					sprintf(
						__( 'Amount must be more than %s', 'give' ),
						formatValue( { value: min, ...formatConfig } ),
					),
				);
			} else {
				// Clear the error
				setValidationError( null );
			}
		} else {
			setValidationError(
				sprintf(
					__( 'Please enter an amount between %s and %s or choose a predefined amount', 'give' ),
					formatValue( { value: min, ...formatConfig } ),
					formatValue( { value: max, ...formatConfig } ),
				),
			)
		}
	}, [ currency.numberDecimals, min, max, value ] );

	const customAmountInputId = toUniqueId();

	return (
		<div className="give-donor-dashboard-amount-inputs">
			<FieldRow>
				<div>
					<SelectControl
						label={ __( 'Subscription Amount', 'give' ) }
						options={ options }
						value={ selectValue }
						onChange={ setSelectValue }
					/>
				</div>
				<div>
					{ selectValue === CUSTOM_AMOUNT && (
						<div className="give-donor-dashboard-currency-control">
							<label className="give-donor-dashboard-currency-control__label" htmlFor={ customAmountInputId }>
								{ __( 'Custom Amount', 'give' ) }
							</label>
							<div className="give-donor-dashboard-currency-control__input">
								<CurrencyInput
									id={ customAmountInputId }
									name="custom-amount"
									placeholder={ __( 'Enter amount...', 'give' ) }
									value={ value ?? '' }
									onValueChange={ onChange }
									onBlur={ validateCustomAmount }
									allowNegativeValue={ false }
									{ ...formatConfig }
								/>
							</div>
						</div>
					) }
				</div>
			</FieldRow>
			{ validationError && (
				<FieldRow>
					<p>{ validationError }</p>
				</FieldRow>
			) }
		</div>
	);
};

export default AmountControl;
