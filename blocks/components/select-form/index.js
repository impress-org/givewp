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

const SelectForm = ( props ) => {
	// Event(s)
	const getFormOptions = () => {
		// Add API Data To Select Options

		let formOptions = [];

		if ( ! isUndefined( props.forms.data ) ) {
			formOptions = props.forms.data.map(
				( form ) => {
					return {
						value: form.id,
						label: form.title.rendered === '' ? `${ form.id } : ${ __( 'No form title' ) }` : form.title.rendered,
					};
				}
			);
		}
		// Add Default option
		formOptions.unshift( giveFormOptionsDefault );

		return formOptions;
	};

	const setFormIdTo = id => {
		props.setAttributes( { id } );
	};

	const resetFormIdTo = () => {
		props.setAttributes( { id: props.attributes.prevId } );
		props.setAttributes( { prevId: 0 } );
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
				props.attributes.prevId &&
				<Button isLarge
					onClick={ resetFormIdTo }>
					{ __( 'Cancel' ) }
				</Button>
			}
		</GiveBlankSlate>
	);
};

export default SelectForm;
