/**
 * External dependencies
 */
import { isEmpty, pickBy, isUndefined } from 'lodash';
import { stringify } from 'querystringify';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const {
	withSelect,
	registerStore,
} = wp.data;

/**
 * Internal dependencies
 */
import GiveBlankSlate from '../../components/blank-slate';
import NoForms from '../../components/no-form';
import EditForm from '../../components/edit-form';
import FormPreview from './form/preview';
import SelectForm from '../../components/select-form';

/**
 * Render Block UI For Editor
 */
const GiveForm = ( props ) => {
	const { attributes, form } = props;
	const { id } = attributes;
	const { data } = form;

	// Render block UI
	let blockUI;

	if ( ! id ) {
		if ( isUndefined( data ) ) {
			blockUI = <GiveBlankSlate title={ __( 'Loading...' ) } isLoader={ true } />;
		} else if ( isEmpty( data ) ) {
			blockUI = <NoForms />;
		} else {
			blockUI = <SelectForm { ... { ...props } } />;
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

const actions = {
	setDonationForm( donationFormData ) {
		return {
			type: 'SET_DONATION_FORM',
			donationFormData,
		};
	},

	getDonationForm( path ) {
		return {
			type: 'RECEIVE_DONATION_FORM',
			path,
		};
	},
};

const store = registerStore( 'give/donation-form', {
	reducer( state = { donationFormData: {} }, action ) {

		switch ( action.type ) {
			case 'SET_DONATION_FORM':
				return {
					...state,
					donationFormData: action.donationFormData,
				};
			case 'RECEIVE_DONATION_FORM':
				return action.donationFormData;
		}

		return state;
	},

	actions,

	selectors: {
		getDonationForm( state ) {
			const { donationFormData } = state;
			return donationFormData;
		},
	},

	resolvers: {
		* getDonationForm( state, id, parameters ) {
			const donationFormData = wp.apiFetch( { path: `/give-api/v2/form/${ id }/?${ parameters }` } )
				.then( donationFormData => {
					return actions.setDonationForm( donationFormData );
				} )
			yield donationFormData;
		},
	},

} );

/**
 * Export component attaching withSelect
 */
export default withSelect( ( select, props ) => {
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
		form: {
			data: select( 'give/donation-form' ).getDonationForm( id, parameters )
		},
		forms: {
			data: []
		}
	}
})( GiveForm )
