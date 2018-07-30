/**
 * Block dependencies
 */
import GiveBlankSlate from '../blank-slate/index';
import isUndefined from 'lodash.isundefined';

/**
 * Internal dependencies
 */
const {__}                    = wp.i18n;
const {SelectControl, Button} = wp.components;
const giveFormOptionsDefault  = { value: '0', label: __( '-- Select Form --' ) };

/**
 * Render form select UI
 */

const FormSelect = (props) => {
	// Event(s)
	const getFormOptions = () => {
		// Add API Data To Select Options

		let formOptions = [];

		if( ! isUndefined( props.forms.data ) ) {
			formOptions = props.forms.data.map(
				(form) => {
					return {
						value: form.id,
						label: form.title.rendered === '' ? `${ form.id } : ${__( 'No form title' ) }` : form.title.rendered,
					};
				}
			);
		}
		// Add Default option
		formOptions.unshift( giveFormOptionsDefault );

		return formOptions;
	};

	const setFormIdTo = id => {
		props.setAttributes( {id} );
	};

	const resetFormIdTo = () => {
		props.setAttributes( {id: props.attributes.prevId} );
		props.setAttributes( {prevId: 0} );
	};

	return (
		<GiveBlankSlate title = {__( 'Give Donation form' )}>
			<SelectControl
				options = {getFormOptions()}
				onChange = {setFormIdTo}
			/>

			<Button isPrimary
					isLarge href = {`${ wpApiSettings.schema.url }/wp-admin/post-new.php?post_type=give_forms`}>
				{__( 'Add New Form' )}
			</ Button>&nbsp;&nbsp;

			{
				props.attributes.prevId &&
				<Button isLarge
						onClick = {resetFormIdTo}>
					{__( 'Cancel' )}
				</ Button>
			}
		</ GiveBlankSlate>
	);
};

export default FormSelect;
