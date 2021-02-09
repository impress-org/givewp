import TextControl from '../../text-control';
import FieldRow from '../../field-row';
import SelectControl from '../../select-control';
import { useState, useEffect } from 'react';

const AmountInputs = ( { form, onChange, value } ) => {
	const [ customAmount, setCustomAmount ] = useState( 0 );
	const [ selectValue, setSelectValue ] = useState( null );
	const [ prevSelectValue, setPrevSelectValue ] = useState( null );
	const [ amountOptions, setAmountOptions ] = useState( [] );

	useEffect( () => {
		const amounts = form.amounts.map( ( amount ) => {
			return parseInt( amount._give_amount ).toString();
		} );

		const options = amounts.map( ( amount ) => {
			return {
				value: amount,
				label: amount,
			};
		} );

		if ( form.custom_amount ) {
			options.push( {
				value: 'custom_amount',
				label: 'Custom Amount',
			} );
		}

		setAmountOptions( options );

		if ( value ) {
			const formatted = parseInt( value ).toString();
			if ( amounts.includes( formatted ) ) {
				setSelectValue( formatted );
			} else {
				setSelectValue( 'custom_amount' );
				setCustomAmount( formatted );
			}
		}
	}, [] );

	useEffect( () => {
		if ( selectValue ) {
			if ( selectValue !== 'custom_amount' ) {
				onChange( selectValue );
				setPrevSelectValue( selectValue );
			} else {
				setCustomAmount( prevSelectValue );
				onChange( customAmount );
			}
		}
	}, [ customAmount, selectValue ] );

	return (
		<div className="give-donor-profile-amount-inputs">
			<FieldRow>
				<div>
					<SelectControl label="Subscription Amount" options={ amountOptions } value={ selectValue } onChange={ ( val ) => setSelectValue( val ) } />
				</div>
				<div>
					{ selectValue === 'custom_amount' && (
						<TextControl label="Custom Amount" value={ customAmount } onChange={ ( val ) => setCustomAmount( val ) } />
					) }
				</div>
			</FieldRow>
		</div>
	);
};

export default AmountInputs;
