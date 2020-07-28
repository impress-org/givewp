// Import vendor dependencies
import PropTypes from 'prop-types';

// Import styles
import './style.scss';

const Step = ( { children } ) => {
	return (
		<div className="give-obw-step" role="main">
			{ children }
		</div>
	);
};

Step.propTypes = {
	children: PropTypes.node.isRequired,
};

Step.defaultProps = {
	children: null,
};

export default Step;
