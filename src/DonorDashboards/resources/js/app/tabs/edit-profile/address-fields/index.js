import { Fragment, useState, useEffect } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { setStates } from '../../../store/actions';
import { __ } from '@wordpress/i18n';

import SelectControl from '../../../components/select-control';
import TextControl from '../../../components/text-control';
import FieldRow from '../../../components/field-row';

import { fetchStatesWithAPI } from '../utils';

const AddressFields = ( { address, onChange } ) => {
	useEffect( () => {
		setCountry( address.country );
		setLine1( address.line1 );
		setLine2( address.line2 );
		setCity( address.city );
		setState( address.state );
		setZip( address.zip );
	}, [ address ] );

	const dispatch = useDispatch();
	const countryOptions = useSelector( state => state.countries );
	const stateOptions = useSelector( state => state.states );

	const [ country, setCountry ] = useState( address.country );
	const [ line1, setLine1 ] = useState( address.line1 );
	const [ line2, setLine2 ] = useState( address.line2 );
	const [ city, setCity ] = useState( address.city );
	const [ state, setState ] = useState( address.state );
	const [ zip, setZip ] = useState( address.zip );

	const updateStates = async( countryCode ) => {
		if ( countryCode ) {
			const newStates = await fetchStatesWithAPI( countryCode );
			dispatch( setStates( newStates ) );
		}
	};

	useEffect( () => {
		updateStates( country );
	}, [ country ] );

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
