import CurrencyControl from '../../currency-control';
import FieldRow from '../../field-row';
import SelectControl from '../../select-control';
import { useState, useEffect } from 'react';

import { __ } from '@wordpress/i18n';

const AmountControl = ( { form, payment, onChange, value } ) => {
	const [ customAmount, setCustomAmount ] = useState( '' );
	const [ selectValue, setSelectValue ] = useState( '' );
	const [ prevSelectValue, setPrevSelectValue ] = useState( '' );
	const [ amountOptions, setAmountOptions ] = useState( [] );

	useEffect( () => {
		const options = form.amounts.map( ( amount ) => {
			return {
				value: amount.raw,
				label: amount.formatted,
			};
		} );

		if ( form.custom_amount ) {
			options.push( {
				value: 'custom_amount',
				label: __( 'Custom Amount', 'give' ),
			} );
		}

		setAmountOptions( options );
	}, [] );

	useEffect( () => {
		if ( amountOptions.length ) {
			const amountFloats = amountOptions.map( ( option ) => {
				return parseFloat( option.value );
			} );
			if ( value ) {
				const float = parseFloat( value );
				if ( amountFloats.includes( float ) ) {
					const option = amountOptions.filter( ( curr ) => parseFloat(curr.value) === float )[ 0 ];
					setSelectValue( option.value );
				} else {
					setSelectValue( 'custom_amount' );
					setCustomAmount( float.toFixed( payment.currency.numberDecimals ) );
				}
			}
		}
	}, [ amountOptions ] );

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
					<SelectControl label="Subscription Amount" options={ amountOptions } value={ selectValue } onChange={ ( val ) => setSelectValue( val ) } />
				</div>
				<div>
					{ selectValue === 'custom_amount' && (
						<CurrencyControl
							label={ __( 'Custom Amount', 'give' ) }
							min={ form.custom_amount.minimum ? parseFloat( form.custom_amount.minimum ).toString() : null }
							max={ form.custom_amount.maximum ? parseFloat( form.custom_amount.maximum ).toString() : null }
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
