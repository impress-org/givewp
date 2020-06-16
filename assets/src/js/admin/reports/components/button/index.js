import PropTypes from 'prop-types';
import './style.scss';

const Button = ( { pressed, children, onClick, type } ) => {
	let className = 'givewp-reports-button';
	if ( type === 'icon' ) {
		className += ' givewp-reports-button--icon';
	}
	if ( pressed ) {
		className += ' givewp-reports-button--pressed';
	}
	return (
		<button className={ className } onClick={ onClick }>
			{ children }
		</button>
	);
};

Button.propTypes = {
	// Button type (ex: icon)
	type: PropTypes.string,
	// Visual state of button (darker when pressed)
	pressed: PropTypes.bool,
	// Button children
	children: PropTypes.node,
	// Fired on button click
	onClick: PropTypes.func,
};
export default Button;
