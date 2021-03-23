import Heading from '../../components/heading';
import Divider from '../../components/divider';
import AvatarControl from '../../components/avatar-control';
import FieldRow from '../../components/field-row';
import SelectControl from '../../components/select-control';
import TextControl from '../../components/text-control';
import RadioControl from '../../components/radio-control';
import Button from '../../components/button';
import { updateProfileWithAPI } from './utils';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

import EmailControls from './email-controls';
import AddressControls from './address-controls';

import { Fragment, useState, useEffect } from 'react';
import { useSelector } from 'react-redux';
import { __ } from '@wordpress/i18n';
;

import './style.scss';

const Content = () => {
	const id = useSelector( state => state.id );
	const storedProfile = useSelector( state => state.profile );
	const [ isUpdating, setIsUpdating ] = useState( false );
	const [ updated, setUpdated ] = useState( false );

	useEffect( () => {
		setAvatarFile( null );
		setAvatarUrl( storedProfile.avatarUrl );
	}, [ storedProfile ] );

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
	const [ avatarUrl, setAvatarUrl ] = useState( storedProfile.avatarUrl );

	const [ firstName, setFirstName ] = useState( storedProfile.firstName );
	const [ lastName, setLastName ] = useState( storedProfile.lastName );

	const [ company, setCompany ] = useState( storedProfile.company );

	const [ primaryEmail, setPrimaryEmail ] = useState( storedProfile.emails ? storedProfile.emails.primary : '' );

	const reducedAdditionalEmails = storedProfile.emails ? Object.keys( storedProfile.emails ).reduce( ( newArray, key ) => {
		if ( key !== 'primary' ) {
			newArray.push( storedProfile.emails[ key ] );
		}
		return newArray;
	}, [] ) : [];

	const [ additionalEmails, setAdditionalEmails ] = useState( reducedAdditionalEmails );

	const [ primaryAddress, setPrimaryAddress ] = useState( storedProfile.addresses && storedProfile.addresses.billing ? storedProfile.addresses.billing[ 0 ] : null );

	const reducedAdditionalAddresses = storedProfile.addresses && storedProfile.addresses.billing ? Object.values( storedProfile.addresses.billing ).reduce( ( newArray, address, index ) => {
		if ( index !== 0 ) {
			newArray.push( address );
		}
		return newArray;
	}, [] ) : [];

	const [ additionalAddresses, setAdditionalAddresses ] = useState( reducedAdditionalAddresses );

	const [ isAnonymous, setIsAnonymous ] = useState( storedProfile.isAnonymous );
	const anonymousOptions = [
		{
			value: '0',
			label: __( 'Public - show my donations publicly', 'give' ),
		},
		{
			value: '1',
			label: __( 'Private - only organization admins can view my info' ),
		},
	];

	useEffect( () => {
		setUpdated( false );
	}, [
		titlePrefix,
		firstName,
		lastName,
		company,
		primaryEmail,
		additionalEmails,
		primaryAddress,
		additionalAddresses,
		isAnonymous,
	] );

	useEffect( () => {
		if ( avatarFile !== null ) {
			setUpdated(false)
		}
	}, [
		avatarFile
	])

	const handleUpdate = async() => {
		setIsUpdating( true );
		await updateProfileWithAPI( {
			titlePrefix,
			firstName,
			lastName,
			company,
			primaryEmail,
			additionalEmails,
			primaryAddress,
			additionalAddresses,
			avatarFile,
			isAnonymous,
			id,
		} );
		setUpdated( true );
		setIsUpdating( false );
	};

	return (
		<Fragment>
			<Heading>
				{ __( 'Profile Information', 'give' ) }
			</Heading>
			<Divider />
			<AvatarControl
				url={ avatarUrl }
				file={ avatarFile }
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
			<TextControl
				label={ __( 'Company', 'give' ) }
				value={ company }
				onChange={ ( value ) => setCompany( value ) }
			/>
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
				description={ __( 'This will prevent your avatar, first name, donation comments, and other information from appearing publicly on this organization’s website.', 'give' ) }
				options={ anonymousOptions }
				onChange={ ( value ) => setIsAnonymous( value ) }
				value={ isAnonymous }
			/>
			<Button onClick={ () => handleUpdate() }>
				{ updated ? (
					<Fragment>
						{ __( 'Updated', 'give' ) } <FontAwesomeIcon icon="check" fixedWidth />
					</Fragment>
				) : (
					<Fragment>
						{ __( 'Update Profile', 'give' ) } <FontAwesomeIcon className={ isUpdating ? 'give-donor-dashboard__edit-profile-spinner' : '' } icon={ isUpdating ? 'spinner' : 'save' } fixedWidth />
					</Fragment>
				) }
			</Button>
		</Fragment>
	);
};
export default Content;
