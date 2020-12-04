import Heading from '../../components/heading';
import Divider from '../../components/divider';
import AvatarControl from '../../components/avatar-control';
import FieldRow from '../../components/field-row';
import SelectControl from '../../components/select-control';
import TextControl from '../../components/text-control';
import RadioControl from '../../components/radio-control';
import Button from '../../components/button';
import { updateProfileWithAPI } from './utils';

import EmailControls from './email-controls';
import AddressControls from './address-controls';

import { Fragment, useState } from 'react';
import { useSelector } from 'react-redux';
const { __ } = wp.i18n;

import './style.scss';

const Content = () => {
	const id = useSelector( state => state.id );
	const storedProfile = useSelector( state => state.profile );

	const [ titlePrefix, setTitlePrefix ] = useState( storedProfile.titlePrefix );
	const titlePrefixOptions = [
		{
			value: 'Mr.',
			label: 'Mr.',
		},
		{
			value: 'Ms.',
			label: 'Ms.',
		},		{
			value: 'Mrs.',
			label: 'Mrs.',
		},
	];

	const [ avatarFile, setAvatarFile ] = useState( null );

	const [ firstName, setFirstName ] = useState( storedProfile.firstName );
	const [ lastName, setLastName ] = useState( storedProfile.lastName );

	const [ primaryEmail, setPrimaryEmail ] = useState( storedProfile.emails.primary );

	const reducedAdditionalEmails = Object.keys( storedProfile.emails ).reduce( ( newArray, key ) => {
		if ( key !== 'primary' ) {
			newArray.push( storedProfile.emails[ key ] );
		}
		return newArray;
	}, [] );

	const [ additionalEmails, setAdditionalEmails ] = useState( reducedAdditionalEmails );

	const [ primaryAddress, setPrimaryAddress ] = useState( storedProfile.addresses.billing ? storedProfile.addresses.billing[ 0 ] : null );

	const reducedAdditionalAddresses = storedProfile.addresses.billing ? storedProfile.addresses.billing.reduce( ( newArray, address, index ) => {
		if ( index !== 0 ) {
			newArray.push( address );
		}
		return newArray;
	}, [] ) : [];
	const [ additionalAddresses, setAdditionalAddresses ] = useState( reducedAdditionalAddresses );

	const [ anonymous, setAnonymous ] = useState( storedProfile.isAnonymous );
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

	const handleUpdate = () => {
		updateProfileWithAPI( {
			titlePrefix,
			firstName,
			lastName,
			primaryEmail,
			additionalEmails,
			primaryAddress,
			additionalAddresses,
			avatarFile,
			id,
		} );
	};

	return (
		<Fragment>
			<Heading>
				{ __( 'Profile Information', 'give' ) }
			</Heading>
			<Divider />
			<AvatarControl
				storedValue={ storedProfile.avatarUrl }
				value={ avatarFile }
				onChange={ ( value ) => setAvatarFile( value ) }
			/>
			<FieldRow>
				<SelectControl
					label={ __( 'Prefix', 'give' ) }
					value={ titlePrefix }
					onChange={ ( value ) => setTitlePrefix( value ) }
					options={ titlePrefixOptions }
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
			<EmailControls
				primaryEmail={ primaryEmail }
				additionalEmails={ additionalEmails }
				onChangePrimaryEmail={ ( value ) => setPrimaryEmail( value ) }
				onChangeAdditionalEmails={ ( value ) => setAdditionalEmails( value ) }
			/>
			<AddressControls
				primaryAddress={ primaryAddress }
				additionalAddresses={ additionalAddresses }
				onChangePrimaryAddress={ ( value ) => setPrimaryAddress( value ) }
				onChangeAdditionalAddresses={ ( value ) => setAdditionalAddresses( value ) }
			/>
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
			<Button icon="save" onClick={ () => handleUpdate() }>
				Update Profile
			</Button>
		</Fragment>
	);
};
export default Content;
