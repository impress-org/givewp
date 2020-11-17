import Heading from '../../components/heading';
import Divider from '../../components/divider';
import AvatarControl from '../../components/avatar-control';
import FieldRow from '../../components/field-row';
import SelectControl from '../../components/select-control';
import TextControl from '../../components/text-control';

import { Fragment, useState } from 'react';
const { __ } = wp.i18n;

const Content = () => {
	const [ prefix, setPrefix ] = useState( null );
	const prefixOptions = [
		{
			value: 'mr',
			label: 'Mr.',
		},
		{
			value: 'ms',
			label: 'Ms.',
		},		{
			value: 'mrs',
			label: 'Mrs.',
		},
	];

	const [ firstName, setFirstName ] = useState( '' );
	const [ lastName, setLastName ] = useState( '' );

	const [ primaryEmail, setPrimaryEmail ] = useState( '' );

	const [ country, setCountry ] = useState( '' );
	const countryOptions = [
		{
			value: 'USA',
			label: 'United States',
		},
		{
			value: 'UK',
			label: 'United Kingdom',
		},		{
			value: 'CAN',
			label: 'Canada',
		},
	];
	const [ addressOne, setAddressOne ] = useState( '' );
	const [ addressTwo, setAddressTwo ] = useState( '' );
	const [ city, setCity ] = useState( '' );
	const [ state, setState ] = useState( '' );
	const stateOptions = [
		{
			value: 'NY',
			label: 'New York',
		},
		{
			value: 'MI',
			label: 'Michigan',
		},		{
			value: 'CA',
			label: 'California',
		},
	];
	const [ zip, setZip ] = useState( '' );

	return (
		<Fragment>
			<Heading>
				Profile Information
			</Heading>
			<Divider />
			<AvatarControl />
			<FieldRow>
				<SelectControl
					label={ __( 'Prefix', 'give' ) }
					value={ prefix }
					onChange={ ( value ) => setPrefix( value ) }
					options={ prefixOptions }
					placeholder="--"
					width="80px"
				/>
				<TextControl
					label={ __( 'First Name', 'give' ) }
					value={ firstName }
					onChange={ ( value ) => setFirstName( value ) }
					icon="user"
				/>
				<TextControl
					label={ __( 'Last Name', 'give' ) }
					value={ lastName }
					onChange={ ( value ) => setLastName( value ) }
				/>
			</FieldRow>
			<TextControl
				label={ __( 'Primary Email', 'give' ) }
				value={ primaryEmail }
				onChange={ ( value ) => setPrimaryEmail( value ) }
				icon="envelope"
			/>
			<Heading>
				Address
			</Heading>
			<Divider />
			<SelectControl
				label={ __( 'Country', 'give' ) }
				value={ country }
				onChange={ ( value ) => setCountry( value ) }
				options={ countryOptions }
				width={ null }
			/>
			<TextControl
				label={ __( 'Address 1', 'give' ) }
				value={ addressOne }
				onChange={ ( value ) => setAddressOne( value ) }
			/>
			<TextControl
				label={ __( 'Address 2', 'give' ) }
				value={ addressTwo }
				onChange={ ( value ) => setAddressTwo( value ) }
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
export default Content;
