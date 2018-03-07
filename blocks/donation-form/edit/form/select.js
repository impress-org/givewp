/**
 * Block dependencies
 */
import giveFormOptions from '../../data/options';
import GiveBlankSlate from '../../../components/blank-slate/index';

/**
 * Internal dependencies
 */
const { __ } = wp.i18n;
const {
	SelectControl,
	Button,
} = wp.components;

/**
 * Render form select UI
 */

const FormSelect = ( props ) => {
	// Event(s)
	const getFormOptions = () => {
		// Add API Data To Select Options
		const formOptions = props.forms.data.map( ( form ) => {
			return {
				value: form.id,
				label: form.title.rendered === '' ? `${ form.id }: No form title` : form.title.rendered,
			};
		} );

		// Add Default option
		formOptions.unshift( giveFormOptions.default );

		return formOptions;
	};

	const setFormIdTo = id => {
		props.setAttributes( { id } );
	};

	return (
		<GiveBlankSlate title={ __( 'Give Donation form' ) }>
			<SelectControl
				options={ getFormOptions() }
				onChange={ setFormIdTo }
			/>

			<Button isPrimary
				isLarge href={ `${ wpApiSettings.schema.url }/wp-admin/post-new.php?post_type=give_forms` }>
				{ __( 'Add new form' ) }
			</Button>
		</GiveBlankSlate>
	);
};

export default FormSelect;
