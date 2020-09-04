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

const Milestone = ( { attributes, setAttributes } ) => {
	return (
		<Fragment>
			<Inspector { ... { attributes, setAttributes } } />
			<ServerSideRender block="give/milestone" attributes={ attributes } />
		</Fragment>
	);
};

export default Milestone;
