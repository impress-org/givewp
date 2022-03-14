// Import vendor dependencies
import PropTypes from 'prop-types';
import { __ } from '@wordpress/i18n'

// Import store dependencies
import { useStoreValue } from '../../app/store';
import { goToStep } from '../../app/store/actions';

// Import utilities
import { redirectToSetupPage } from '../../utils';

// Import components
import Button from '../button';
import Chevron from '../icons/chevron';

// Import styles
import './style.scss';

const ContinueButton = ( { label, testId, clickCallback } ) => {
	const [ { currentStep, lastStep }, dispatch ] = useStoreValue();

	return (
		<Button testId={ testId } onClick={ () => {
			clickCallback();
			if ( currentStep + 1 <= lastStep ) {
				dispatch( goToStep( currentStep + 1 ) );
			} else {
				redirectToSetupPage();
			}
		} }>
			{ label }
			<span className="give-obw-continue-button__icon">
				<Chevron />
			</span>
		</Button>
	);
};

ContinueButton.propTypes = {
	label: PropTypes.string,
	testId: PropTypes.string,
	clickCallback: PropTypes.func,
};

ContinueButton.defaultProps = {
	label: __( 'Continue', 'give' ),
	testId: null,
	clickCallback: () => {},
};

export default ContinueButton;
