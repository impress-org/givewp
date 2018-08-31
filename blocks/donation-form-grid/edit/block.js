/**
 * External dependencies
 */
import { isEmpty, pickBy, isUndefined } from 'lodash';
import { stringify } from 'querystringify';

/**
 * Wordpress dependencies
 */
const { __ } = wp.i18n;
const { withAPIData } = wp.components;

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
	const { latestForms } = props;
	const { isLoading, data } = latestForms;

	// Render block UI
	let blockUI;

	if ( isLoading || isUndefined( data ) ) {
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

/**
 * Export component attaching withAPIdata
 */
export default withAPIData( ( props ) => {
	const { columns, showGoal, showExcerpt, showFeaturedImage, displayType } = props.attributes;

	const parameters = stringify( pickBy( {
		columns: columns,
		show_goal: showGoal,
		show_excerpt: showExcerpt,
		show_featured_image: showFeaturedImage,
		display_type: displayType,
	}, value => ! isUndefined( value )
	) );

	return {
		latestForms: `/${ giveApiSettings.rest_base }/form-grid/?${ parameters }`,
	};
} )( GiveDonationFormGrid );
