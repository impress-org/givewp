import { Fragment, useState, useEffect } from 'react';
const { __ } = wp.i18n;

import SelectControl from '../../../components/select-control';
import TextControl from '../../../components/text-control';
import FieldRow from '../../../components/field-row';

const AddressFields = ( { address, onChange } ) => {
	const [ country, setCountry ] = useState( address.country );
	const countryOptions = [
		{
			value: 'US',
			label: 'United States',
		},
		{
			value: 'UK',
			label: 'United Kingdom',
		},		{
			value: 'CA',
			label: 'Canada',
		},
	];
	const [ line1, setLine1 ] = useState( address.line1 );
	const [ line2, setLine2 ] = useState( address.line2 );
	const [ city, setCity ] = useState( address.city );
	const [ state, setState ] = useState( address.state );
	const stateOptions = [
		{
			value: 'NY',
			label: 'New York',
		},
		{
			value: 'MI',
			label: 'Michigan',
		},		{
			value: 'TX',
			label: 'Texas',
		},
	];
	const [ zip, setZip ] = useState( address.zip );

	useEffect( () => {
		const newAddress = {
			country,
			line1,
			line2,
			state,
			city,
			zip,
		};
		onChange( newAddress );
	}, [ country, line1, line2, state, city, zip ] );

	return (
		<Fragment>
			<SelectControl
				label={ __( 'Country', 'give' ) }
				value={ country }
				onChange={ ( value ) => setCountry( value ) }
				options={ countryOptions }
				width={ null }
			/>
			<TextControl
				label={ __( 'Address 1', 'give' ) }
				value={ line1 }
				onChange={ ( value ) => setLine1( value ) }
			/>
			<TextControl
				label={ __( 'Address 2', 'give' ) }
				value={ line2 }
				onChange={ ( value ) => setLine2( value ) }
			/>
			<TextControl
				label={ __( 'City', 'give' ) }
				value={ city }
				onChange={ ( value ) => setCity( value ) }
			/>
			<FieldRow>
				<SelectControl
					label={ __( 'State', 'give' ) }
					value={ state }
					onChange={ ( value ) => setState( value ) }
					options={ stateOptions }
				/>
				<TextControl
					label={ __( 'Zip', 'give' ) }
					value={ zip }
					onChange={ ( value ) => setZip( value ) }
				/>
			</FieldRow>
		</Fragment>
	);
};

export default AddressFields;
