/**
 * External dependencies
 */
import { isEmpty, pickBy, isUndefined } from 'lodash';
import { stringify } from 'querystringify';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { withAPIData } = wp.components;

/**
 * Internal dependencies
 */
import GiveBlankSlate from '../../components/blank-slate';
import NoForms from '../../components/no-form';
import EditForm from '../../components/edit-form';
import FormPreview from './form/preview';
import FormSelect from '../../components/form-select';

/**
 * Render Block UI For Editor
 */

const GiveForm = ( props ) => {
	const { attributes, form } = props;
	const { id } = attributes;
	const { isLoading, data } = form;

	// Render block UI
	let blockUI;

	if ( ! id ) {
		if ( isLoading || isUndefined( data ) ) {
			blockUI = <GiveBlankSlate title={ __( 'Loading...' ) } isLoader={ true } />;
		} else if ( isEmpty( data ) ) {
			blockUI = <NoForms />;
		} else {
			blockUI = <FormSelect { ... { ...props } } />;
		}
	} else if ( isEmpty( data ) ) {
		blockUI = isLoading ?
			<GiveBlankSlate title={ __( 'Loading...' ) } isLoader={ true } /> :
			<EditForm formId={ id } { ... { ...props } } />;
	} else {
		blockUI = <FormPreview
			html={ data }
			{ ... { ...props } } />;
	}

	return (
		<div className={ !! props.isSelected ? `${ props.className } isSelected` : props.className } key="GiveBlockUI">
			{ blockUI }
		</div>
	);
};

/**
 * Export component attaching withAPIdata
 */
export default withAPIData( ( props ) => {
	const { showTitle, showGoal, showContent, displayStyle, continueButtonTitle, id } = props.attributes;

	let parameters = {
		show_title: showTitle,
		show_goal: showGoal,
		show_content: showContent,
		display_style: displayStyle,
	};

	if ( 'reveal' === displayStyle ) {
		parameters.continue_button_title = continueButtonTitle;
	}

	parameters = stringify( pickBy( parameters, value => ! isUndefined( value ) ) );

	return {
		form: `/${ giveApiSettings.rest_base }/form/${ id }/?${ parameters }`,
		forms: '/wp/v2/give_forms',
	};
} )( GiveForm );
