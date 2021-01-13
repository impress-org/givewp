// Import vendor dependencies
import PropTypes from 'prop-types';

// Import store dependencies
import { useStoreValue } from '../../app/store';

// Import utilities
import { goToStep } from '../../app/store/actions';

// Import components
import Checkmark from '../checkmark';

// Import styles
import './style.scss';

const StepLink = ( { title, stepNumber } ) => {
	const [ { currentStep }, dispatch ] = useStoreValue();
	const progressBarStyle = {
		width: currentStep <= stepNumber ? '0%' : '100%',
	};

	return (
		<div className="give-obw-step-link" data-givewp-test="navigation-step">
			<button className="give-obw-step-button" onClick={ () => dispatch( goToStep( stepNumber ) ) }>
				<div className={ `give-obw-step-icon${ currentStep >= stepNumber ? ' give-obw-step-icon--green' : '' }` }>
					{ currentStep <= stepNumber ? stepNumber : <Checkmark index={ stepNumber } /> }
				</div>
				<div className="give-obw-step-title">
					{ title }
				</div>
			</button>
			<div className="give-obw-step-progress">
				<div className="give-obw-step-progress-bar" style={ progressBarStyle }></div>
			</div>
		</div>
	);
};

StepLink.propTypes = {
	title: PropTypes.string.isRequired,
	stepNumber: PropTypes.number.isRequired,
};

StepLink.defaultProps = {
	title: null,
	stepNumber: null,
};

export default StepLink;
