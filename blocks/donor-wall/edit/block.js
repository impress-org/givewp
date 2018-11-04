/**
 * Wordpress dependencies
 */
const { ServerSideRender } = wp.components;

/**
 * Internal dependencies
 */
import Inspector from './inspector';

/**
 * Render Block UI For Editor
 */

const GiveDonorWall = ( props ) => {
	const { className, attributes } = props;

	return (
		<div className={ className }>
			<div id="donation-form-grid-preview-block">
				<Inspector { ... { ...props } } />
				<ServerSideRender block="give/donor-wall" attributes={ attributes } />
			</div>
		</div>
	);
};

export default GiveDonorWall;
