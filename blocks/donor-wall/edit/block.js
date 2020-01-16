/**
 * Wordpress dependencies
 */
const { Fragment } = wp.element;
const ServerSideRender = wp.serverSideRender;

/**
 * Internal dependencies
 */
import Inspector from './inspector';

/**
 * Render Block UI For Editor
 */

const GiveDonorWall = ( props ) => {
	const { attributes } = props;

	return (
		<Fragment>
			<Inspector { ... { ...props } } />
			<ServerSideRender block="give/donor-wall" attributes={ attributes } />
		</Fragment>
	);
};

export default GiveDonorWall;

// @todo show no donor template if donor does not exist.

