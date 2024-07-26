// Import vendor dependencies
import PropTypes from 'prop-types';
import {__} from '@wordpress/i18n';

// Import store dependencies
import {useStoreValue} from '../../app/store';
import {goToStep} from '../../app/store/actions';

// Import utilities
// Import components
import Button from '../button';
import Chevron from '../icons/chevron';

// Import styles
import './style.scss';

const PreviousButton = ({label, testId, clickCallback}) => {
    const [{currentStep}, dispatch] = useStoreValue();

    if (currentStep === 0) {
        return;
    }

    return (
        <Button
            className="give-obw-button--reverse give-obw-button--secondary"
            testId={testId}
            onClick={() => {
                clickCallback();
                dispatch(goToStep(currentStep - 1));
            }}
        >
            {label}
            <span className="give-obw-previous-button__icon">
                <Chevron />
            </span>
        </Button>
    );
};

PreviousButton.propTypes = {
    label: PropTypes.string,
    testId: PropTypes.string,
    clickCallback: PropTypes.func,
};

PreviousButton.defaultProps = {
    label: __('Previous', 'give'),
    testId: null,
    clickCallback: () => {},
};

export default PreviousButton;
