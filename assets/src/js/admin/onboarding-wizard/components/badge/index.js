// Import vendor dependencies
import PropTypes from 'prop-types';

// Import styles
import './style.scss';

const Badge = ( { label } ) => {
	return (
		<div className="give-obw-badge">
			{ label }
		</div>
	);
};

Badge.propTypes = {
	label: PropTypes.string.isRequired,
};

Badge.defaultProps = {
	label: null,
};

export default Badge;
