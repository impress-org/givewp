/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';
import {withSelect} from '@wordpress/data';

/**
 * Internal dependencies
 */
import Inspector from './inspector';

/**
 * Render Block UI For Editor
 */

const GiveDonationFormGrid = ( props ) => {
	const { attributes } = props;

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
