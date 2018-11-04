/**
 * External dependencies
 */
import { isUndefined } from 'lodash';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { SelectControl, Button } = wp.components;

/**
 * Internal dependencies
 */
import { getSiteUrl } from '../../utils';
import GiveBlankSlate from '../blank-slate';

/**
 * Render form select UI
 */
const giveFormOptionsDefault = { value: '0', label: __( '-- Select Form --' ) };

const SelectForm = ( { forms, attributes, setAttributes } ) => {
	//Attributes
	const { prevId } = attributes;

	// Event(s)
	const getFormOptions = () => {
		// Add API Data To Select Options

		let formOptions = [];

		if ( ! isUndefined( forms ) ) {
			formOptions = forms.map(
				( { id, title: { rendered: title } } ) => {
					return {
						value: id,
						label: title === '' ? `${ id } : ${ __( 'No form title' ) }` : title,
					};
				}
			);
		}
		// Add Default option
		formOptions.unshift( giveFormOptionsDefault );

		return formOptions;
	};

	const setFormIdTo = id => {
		setAttributes( { id: Number( id ) } );
	};

	const resetFormIdTo = () => {
		setAttributes( { id: Number( prevId ) } );
		setAttributes( { prevId: undefined } );
	};

	return (
		<GiveBlankSlate title={ __( 'Give Donation form' ) }>
			<SelectControl
				options={ getFormOptions() }
				onChange={ setFormIdTo }
			/>

			<Button isPrimary
				isLarge href={ `${ getSiteUrl() }/wp-admin/post-new.php?post_type=give_forms` }>
				{ __( 'Add New Form' ) }
			</Button>&nbsp;&nbsp;

			{
				prevId &&
				<Button isLarge
					onClick={ resetFormIdTo }>
					{ __( 'Cancel' ) }
				</Button>
			}
		</GiveBlankSlate>
	);
};

export default SelectForm;
