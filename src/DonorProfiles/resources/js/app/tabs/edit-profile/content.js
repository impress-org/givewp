import Heading from '../../components/heading';
import Divider from '../../components/divider';
import AvatarControl from '../../components/avatar-control';
import FieldRow from '../../components/field-row';
import SelectControl from '../../components/select-control';
import TextControl from '../../components/text-control';
import RadioControl from '../../components/radio-control';
import Button from '../../components/button';

import { Fragment, useState } from 'react';
const { __ } = wp.i18n;

import './style.scss';

const Content = () => {
	const [ prefix, setPrefix ] = useState( 'mr' );
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

	const [ firstName, setFirstName ] = useState( 'Robin' );
	const [ lastName, setLastName ] = useState( 'Hood' );

	const [ primaryEmail, setPrimaryEmail ] = useState( 'robin@merrymen.biz' );
	const [ additionalEmail, setAdditionalEmail ] = useState( 'give2th3p00r@sherwood.net' );

	const [ country, setCountry ] = useState( 'UK' );
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
	const [ addressOne, setAddressOne ] = useState( '12 King John Way' );
	const [ addressTwo, setAddressTwo ] = useState( 'Unit B' );
	const [ city, setCity ] = useState( 'Sherwood Forest' );
	const [ state, setState ] = useState( 'NY' );
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
	const [ zip, setZip ] = useState( '01234' );

	const [ anonymous, setAnonymous ] = useState( 'public' );
	const anonymousOptions = [
		{
			value: 'public',
			label: __( 'Public - show my donations publicly', 'give' ),
		},
		{
			value: 'private',
			label: __( 'Private - only organization admins can view my info' ),
		},
	];

	return (
		<Fragment>
			<Heading>
				{ __( 'Profile Information', 'give' ) }
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
					width="120px"
					isClearable={ true }
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
			<FieldRow>
				<TextControl
					label={ __( 'Additional Emails', 'give' ) }
					value={ additionalEmail }
					onChange={ ( value ) => setAdditionalEmail( value ) }
					icon="envelope"
				/>
				<div className="give-donor-profile__email-controls">
					<div className="give-donor-profile__make-primary-email">
						{ __( 'Make Primary', 'give' ) }
					</div>
					|
					<div className="give-donor-profile__delete-email">
						{ __( 'Delete', 'give' ) }
					</div>
				</div>
			</FieldRow>
			<Heading>
				{ __( 'Address', 'give' ) }
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
			<Heading>
				{ __( 'Additional Info', 'give' ) }
			</Heading>
			<Divider />
			<RadioControl
				label={ __( 'Anonymous Giving' ) }
				description={ __( 'This will prevent your avatar, first name, and donation comments and other information from appearing publicly on this orgization’s website.', 'give' ) }
				options={ anonymousOptions }
				onChange={ ( value ) => setAnonymous( value ) }
				value={ anonymous }
			/>
			<Button icon="save">
				Update Profile
			</Button>
		</Fragment>
	);
};
export default Content;
