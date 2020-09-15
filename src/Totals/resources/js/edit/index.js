/**
 * WordPress dependencies
 */
const ServerSideRender = wp.serverSideRender;
const { Fragment } = wp.element;

/**
 * Internal dependencies
 */
import Inspector from './inspector';

/**
 * Render Block UI For Editor
 */

const Totals = ( { attributes, setAttributes } ) => {
	return (
		<Fragment>
			<Inspector { ... { attributes, setAttributes } } />
			<ServerSideRender block="give/totals" attributes={ attributes } />
		</Fragment>
	);
};

export default Totals;
