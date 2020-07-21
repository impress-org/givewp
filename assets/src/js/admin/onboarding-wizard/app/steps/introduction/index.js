// Import vendor dependencies
const { __ } = wp.i18n;

// Import utilities
import { getWindowData } from '../../../utils';

// Import styles
import './style.scss';

import Card from '../../../components/card';
import GiveLogo from '../../../components/give-logo';
import ContinueButton from '../../../components/continue-button';

const Introduction = () => {
	const setupUrl = getWindowData( 'setupUrl' );
	return (
		<div className="give-obw-introduction">
			<Card>
				<div className="give-obw-introduction__content">
					<GiveLogo />
					<p>
						{ __( 'You’re only minutes away from having a fully functional online donation platform on your website. We recommend using the setup wizard if this is your first time using Give.', 'give' ) }
					</p>
					<ContinueButton />
				</div>
			</Card>
			<a href={ setupUrl }>{ __( 'Dismiss Setup Wizard', 'give' ) }</a>
		</div>
	);
};

export default Introduction;
