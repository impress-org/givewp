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
const { Component } = wp.element;

/**
 * Internal dependencies
 */
import GiveBlankSlate from '../../components/blank-slate';
import NoForms from '../../components/no-form';
import FormGridPreview from './components/preview';

/**
 * Render Block UI For Editor
 *
 * @class GiveDonationFormGrid
 * @extends {Component}
 */
class GiveDonationFormGrid extends Component {
	constructor( props ) {
		super( ...props );
	}

	/**
	 * Render block UI
	 *
	 * @returns {object} JSX Object
	 * @memberof GiveDonationFormGrid
	 */
	render() {
		const props = this.props,
			{ latestForms } = props,
			{ isLoading } = latestForms;

		// Render block UI
		let blockUI;

		if ( isLoading || isUndefined( latestForms.data ) ) {
			blockUI = <GiveBlankSlate title={ __( 'Loading...' ) } isLoader={ true } />;
		} else if ( isEmpty( latestForms.data ) ) {
			blockUI = <NoForms />;
		} else {
			blockUI = <FormGridPreview
				html={ latestForms.data }
				{ ... { ...props } } />;
		}

		return ( <div className={ props.className } key="GiveDonationFormGridBlockUI">{ blockUI }</div> );
	}
}

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
