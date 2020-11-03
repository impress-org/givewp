/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { Fragment } = wp.element;

/**
 * Internal dependencies
 */
import Inspector from './inspector';

const edit = ( { attributes, setAttributes } ) => {
	return (
		<Fragment>
			<Inspector { ... { attributes, setAttributes } } />
			<div className="give-donor-profile">
				<h2>{ __( 'Donor Profile!', 'give' ) }</h2>
			</div>
		</Fragment>
	);
};
export default edit;
