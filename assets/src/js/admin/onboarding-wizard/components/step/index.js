import PropTypes from 'prop-types';
import './style.scss';

const Step = ( { children } ) => {
	return (
		<div className="give-obw-step">
			{ children }
		</div>
	);
};

Step.propTypes = {
	children: PropTypes.node.isRequired,
	showInNavigation: PropTypes.bool,
};

Step.defaultProps = {
	children: null,
	showInNavigation: false,
};

export default Step;
