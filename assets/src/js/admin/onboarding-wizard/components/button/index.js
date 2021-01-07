// Import vendor dependencies
import PropTypes from 'prop-types';

// Import styles
import './style.scss';

const Button = ( { onClick, testId, children } ) => {
	return (
		<button className="give-obw-button" data-givewp-test={ testId } onClick={ onClick }>
			{ children }
		</button>
	);
};

Button.propTypes = {
	onClick: PropTypes.func,
	testId: PropTypes.string,
	children: PropTypes.node,
};

Button.defaultProps = {
	onClick: null,
	testId: null,
	children: null,
};

export default Button;
