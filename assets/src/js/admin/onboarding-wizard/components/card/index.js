// Import vendor dependencies
import PropTypes from 'prop-types';

// Import styles
import './style.scss';

const Card = ( { padding, children } ) => {
	return (
		<div className="give-obw-card" style={ { padding: padding } }>
			{ children }
		</div>
	);
};

Card.propTypes = {
	padding: PropTypes.string,
	children: PropTypes.node,
};

Card.defaultProps = {
	padding: '40px 60px',
	children: null,
};

export default Card;
