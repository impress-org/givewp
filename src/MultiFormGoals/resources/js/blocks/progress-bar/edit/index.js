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

const ProgressBar = ( { attributes, setAttributes } ) => {
	return (
		<Fragment>
			<Inspector { ... { attributes, setAttributes } } />
			<ServerSideRender block="give/progress-bar" attributes={ attributes } />
		</Fragment>
	);
};

export default ProgressBar;
