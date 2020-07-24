// Import vendor dependencies
import PropTypes from 'prop-types';
const { __ } = wp.i18n;

// Import store dependencies
import { useStoreValue } from '../../app/store';
import { goToStep } from '../../app/store/actions';

// Import utilities
import { redirectToSetupPage } from '../../utils';

// Import components
import Button from '../button';
import Chevron from '../chevron';

// Import styles
import './style.scss';

const ContinueButton = ( { label } ) => {
	const [ { currentStep, lastStep }, dispatch ] = useStoreValue();

	return (
		<Button onClick={ () => {
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
};

ContinueButton.defaultProps = {
	label: __( 'Continue', 'give' ),
};

export default ContinueButton;
