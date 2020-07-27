// Import vendor dependencies
import PropTypes from 'prop-types';

// Import styles
import './style.scss';

const Card = ( { children } ) => {
	return (
		<div className="give-obw-card">
			{ children }
		</div>
	);
};

Card.propTypes = {
	children: PropTypes.node,
};

Card.defaultProps = {
	children: null,
};

export default Card;
