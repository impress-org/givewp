import CurrencyControl from '../../currency-control';
import FieldRow from '../../field-row';
import SelectControl from '../../select-control';
import { useState, useEffect } from 'react';

import { __ } from '@wordpress/i18n';

/**
 * This control provides preset options however it allows the user to specify a
 * custom option.
 */
const AmountControl = ( { form, payment, onChange, value, options, min, max } ) => {
	const [ customAmount, setCustomAmount ] = useState( '' );
	const [ selectValue, setSelectValue ] = useState( '' );
	const [ prevSelectValue, setPrevSelectValue ] = useState( '' );

	useEffect( () => {
		if ( options.length ) {
			const amountFloats = options.map( ( option ) => {
				return parseFloat( option.value );
			} );
			if ( value ) {
				const float = parseFloat( value );
				if ( amountFloats.includes( float ) ) {
					const option = options.filter( ( curr ) => parseFloat(curr.value) === float )[ 0 ];
					setSelectValue( option.value );
				} else {
					setSelectValue( 'custom_amount' );
					setCustomAmount( float.toFixed( payment.currency.numberDecimals ) );
				}
			}
		}
	}, [ options ] );

	useEffect( () => {
		if ( selectValue ) {
			if ( selectValue !== 'custom_amount' ) {
				onChange( selectValue );
				setPrevSelectValue( selectValue );
			} else if ( prevSelectValue ) {
				setCustomAmount( parseFloat( prevSelectValue ).toFixed( payment.currency.numberDecimals ) );
			}
		}
	}, [ selectValue ] );

	useEffect( () => {
		if ( customAmount ) {
			const float = parseFloat( customAmount );
			onChange( float );
		}
	}, [ customAmount ] );

	return (
		<div className="give-donor-dashboard-amount-inputs">
			<FieldRow>
				<div>
					<SelectControl label="Subscription Amount" options={ options } value={ selectValue } onChange={ ( val ) => setSelectValue( val ) } />
				</div>
				<div>
					{ selectValue === 'custom_amount' && (
						<CurrencyControl
							label={ __( 'Custom Amount', 'give' ) }
							min={ min }
							max={ max }
							value={ customAmount }
							onChange={ ( val ) => setCustomAmount( val ) } currency={ payment.currency }
						/>
					) }
				</div>
			</FieldRow>
		</div>
	);
};

export default AmountControl;
