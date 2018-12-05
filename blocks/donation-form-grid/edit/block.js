/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

/**
 * WordPress dependencies
 */
const { Fragment } = wp.element;
const { ServerSideRender } = wp.components;
const { withSelect } = wp.data;

/**
 * Internal dependencies
 */
import NoForms from '../../components/no-form';
import Inspector from './inspector';

/**
 * Render Block UI For Editor
 */

const GiveDonationFormGrid = ( props ) => {
	const {attributes} = props;
	const {forms}      = attributes;

	if( ! forms ) {
		return <NoForms { ... { ...props } } />
	}

	return (
		<Fragment>
			<Inspector { ... { ...props } } />
			<ServerSideRender block="give/donation-form-grid" attributes={ attributes } />
		</Fragment>
	);
};

export default withSelect( ( select ) => {
	return {
		forms: select( 'core' ).getEntityRecords( 'postType', 'give_forms' ),
	};
} )( GiveDonationFormGrid );

// @todo show no form template if forms does not exist.
