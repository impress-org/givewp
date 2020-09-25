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

const MultiFormGoals = ( { attributes, setAttributes } ) => {
	return (
		<Fragment>
			<Inspector { ... { attributes, setAttributes } } />
			<ServerSideRender block="give/multi-form-goals" attributes={ attributes } />
		</Fragment>
	);
};

export default MultiFormGoals;
