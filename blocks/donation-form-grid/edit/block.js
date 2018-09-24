/**
 * External dependencies
 */
import { isEmpty, pickBy, isUndefined } from 'lodash';
import { stringify } from 'querystringify';

/**
 * Wordpress dependencies
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
import FormGridPreview from './components/preview';

/**
 * Render Block UI For Editor
 */

const GiveDonationFormGrid = ( props ) => {
	const { formGridData } = props;
	const { data } = formGridData;

	// Render block UI
	let blockUI;

	if ( isUndefined( data ) ) {
		blockUI = <GiveBlankSlate title={ __( 'Loading...' ) } isLoader={ true } />;
	} else if ( isEmpty( data ) ) {
		blockUI = <NoForms />;
	} else {
		blockUI = <FormGridPreview
			html={ data }
			{ ... { ...props } } />;
	}

	return ( <div className={ props.className } key="GiveDonationFormGridBlockUI">{ blockUI }</div> );
};

const actions = {
	setFormGrid( formGridData ) {
		return {
			type: 'SET_FORM_GRID',
			formGridData,
		};
	},

	getFormGrid( path ) {
		return {
			type: 'RECEIVE_FORM_GRID',
			path,
		};
	},
};

const store = registerStore( 'give/donation-form-grid', {
	reducer( state = { formGridData: {} }, action ) {

		switch ( action.type ) {
			case 'SET_FORM_GRID':
				return {
					...state,
					formGridData: action.formGridData,
				};
			case 'RECEIVE_FORM_GRID':
				return action.formGridData;
		}

		return state;
	},

	actions,

	selectors: {
		getFormGrid( state ) {
			const { formGridData } = state;
			return formGridData;
		},
	},

	resolvers: {
		* getFormGrid( state, parameters ) {
			const formGridData = wp.apiFetch( { path: `/give-api/v2/form-grid/?${ parameters }` } )
				.then( formGridData => {
					return actions.setFormGrid( formGridData );
				} )
			yield formGridData;
		},
	},

} );

/**
 * Export component attaching withSelect
 */
export default withSelect( ( select, props ) => {
	const { columns, showGoal, showExcerpt, showFeaturedImage, displayType } = props.attributes;

	const parameters = stringify( pickBy( {
		columns: columns,
		show_goal: showGoal,
		show_excerpt: showExcerpt,
		show_featured_image: showFeaturedImage,
		display_type: displayType,
		},
		value => ! isUndefined( value )
	) );

	return {
		formGridData: {
			data: select( 'give/donation-form-grid' ).getFormGrid( parameters )
		}
	}
})( GiveDonationFormGrid )
