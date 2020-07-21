import PropTypes from 'prop-types';
import './style.scss';

const Button = ( { onClick, children } ) => {
	return (
		<button className="give-obw-button" onClick={ onClick }>
			{ children }
		</button>
	);
};

Button.propTypes = {
	onClick: PropTypes.func,
	children: PropTypes.node,
};

Button.defaultProps = {
	onClick: null,
	children: null,
};

export default Button;
