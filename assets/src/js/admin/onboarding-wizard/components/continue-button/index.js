// Import styles
import './style.scss';

const { __ } = wp.i18n;

import { useStoreValue } from '../../app/store';
import { goToStep } from '../../app/store/actions';

import Button from '../button';

const ContinueButton = () => {
	const [ { currentStep }, dispatch ] = useStoreValue();

	const chevron = <svg width="8" height="13" viewBox="0 0 8 13" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M7.07257 7.21484C7.31866 6.96875 7.31866 6.55859 7.07257 6.3125L1.76788 0.980469C1.49445 0.734375 1.08429 0.734375 0.838196 0.980469L0.20929 1.60938C-0.0368042 1.85547 -0.0368042 2.26562 0.20929 2.53906L4.42023 6.75L0.20929 10.9883C-0.0368042 11.2617 -0.0368042 11.6719 0.20929 11.918L0.838196 12.5469C1.08429 12.793 1.49445 12.793 1.76788 12.5469L7.07257 7.21484Z" fill="white" />
	</svg>;

	return (
		<Button onClick={ () => dispatch( goToStep( currentStep + 1 ) ) }>
			{ __( 'Continue', 'give' ) }
			<span className="give-obw-continue-button__icon">
				{ chevron }
			</span>
		</Button>
	);
};

export default ContinueButton;
