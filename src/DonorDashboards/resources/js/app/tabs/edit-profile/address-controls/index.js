import { Fragment } from 'react';
import { __ } from '@wordpress/i18n';

import AddressFields from '../address-fields';

import Button from '../../../components/button';
import FieldRow from '../../../components/field-row';
import Heading from '../../../components/heading';
import Divider from '../../../components/divider';

const AddressControls = ( { primaryAddress, additionalAddresses, onChangePrimaryAddress, onChangeAdditionalAddresses } ) => {
	const setAdditionalAddress = ( newAddress, index ) => {
		const newAdditionalAddresses = additionalAddresses.concat();
		newAdditionalAddresses[ index ] = newAddress;
		onChangeAdditionalAddresses( newAdditionalAddresses );
	};

	const removeAdditionalAddress = ( remove ) => {
		const newAdditionalAddresses = additionalAddresses.filter( ( address, index ) => index !== remove );
		onChangeAdditionalAddresses( newAdditionalAddresses );
	};

	const addAdditionalAddress = ( newAddress ) => {
		const newAdditionalAddresses = additionalAddresses.concat( newAddress );
		onChangeAdditionalAddresses( newAdditionalAddresses );
	};

	const setPrimaryAddress = ( newAddress ) => {
		onChangePrimaryAddress( newAddress );
	};

	const makePrimaryAddress = async( newAddress, index ) => {
		const oldPrimaryAddress = Object.assign( {}, primaryAddress );
		setPrimaryAddress( newAddress );
		setAdditionalAddress( oldPrimaryAddress, index );
	};

	const additionalAddressControls = additionalAddresses ? additionalAddresses.map( ( address, index ) => {
		return (
			<Fragment key={ index }>
				<FieldRow>
					<Heading>
						{ __( 'Additional Address', 'give' ) }
					</Heading>
					<div className="give-donor-dashboard__address-controls">
						<div className="give-donor-dashboard__make-primary-address" onClick={ () => makePrimaryAddress( address, index ) }>
							{ __( 'Make Primary', 'give' ) }
						</div>
						|
						<div className="give-donor-dashboard__delete-address" onClick={ () => removeAdditionalAddress( index ) }>
							{ __( 'Delete', 'give' ) }
						</div>
					</div>
				</FieldRow>
				<Divider />
				<AddressFields
					address={ address }
					onChange={ ( value ) => setAdditionalAddress( value, index ) }
				/>
			</Fragment>
		);
	} ) : null;

	return primaryAddress ? (
		<Fragment>
			<Heading>
				{ __( 'Primary Address', 'give' ) }
			</Heading>
			<Divider />
			<AddressFields
				address={ primaryAddress }
				onChange={ ( value ) => setPrimaryAddress( value ) }
			/>
			{ additionalAddressControls }
			<Button onClick={ () => addAdditionalAddress( {} ) } icon="plus">
				{ __( 'Add Address', 'give' ) }
			</Button>
		</Fragment>
	) : (
		<div className="give-donor-dashboard__add-primary-address">
			<Heading>
				{ __( 'Looks like you have not set up an address!', 'give' ) }
			</Heading>
			<Button onClick={ () => setPrimaryAddress( {} ) } icon="plus">
				{ __( 'Add Address', 'give' ) }
			</Button>
		</div>
	);
};
export default AddressControls;
