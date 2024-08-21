// Import vendor dependencies
import PropTypes from 'prop-types';
import cx from 'classnames';

// Import store dependencies
import {useStoreValue} from '../../app/store';

// Import utilities
// Import components
import Checkmark from '../checkmark';

// Import styles
import './style.scss';

const StepLink = ({title, stepNumber}) => {
    const [{currentStep}, dispatch] = useStoreValue();
    const progressBarStyle = {
        width: currentStep <= stepNumber ? '0%' : '100%',
    };

    return (
        <div className="give-obw-step-link" data-givewp-test="navigation-step">
            <div className={cx('give-obw-step-button', {'give-obw-step-button--current': currentStep === stepNumber})}>
                <div className={cx('give-obw-step-icon', {'give-obw-step-icon--done': currentStep > stepNumber})}>
                    {currentStep <= stepNumber ? stepNumber : <Checkmark index={stepNumber} />}
                </div>
                <div className="give-obw-step-title">{title}</div>
            </div>
            <div className="give-obw-step-progress">
                <div className="give-obw-step-progress-bar" style={progressBarStyle}></div>
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
