// Import vendor dependencies
import PropTypes from 'prop-types';

// Import styles
import './style.scss';

const Step = ( { showInNavigation, children } ) => {
	const stepStyles = {
		paddingTop: showInNavigation ? '128px' : '52px',
		minHeight: showInNavigation ? 'calc(100vh - 190px)' : 'calc(100vh - 110px)',
	};

	return (
		<div className="give-obw-step" role="main" style={ stepStyles }>
			{ children }
		</div>
	);
};

Step.propTypes = {
	showInNavigation: PropTypes.bool,
	children: PropTypes.node.isRequired,
};

Step.defaultProps = {
	showInNavigation: true,
	children: null,
};

export default Step;
