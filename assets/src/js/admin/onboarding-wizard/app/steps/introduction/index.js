// Import vendor dependencies
import { __ } from '@wordpress/i18n'

// Import styles
import './style.scss';

// Import utilities
import { generateFormPreviewWithOnboardingAPI } from '../../../utils';

import Card from '../../../components/card';
import GiveLogo from '../../../components/give-logo';
import ContinueButton from '../../../components/continue-button';
import DismissLink from '../../../components/dismiss-link';

const Introduction = () => {
	const onStartSetup = () => {
		generateFormPreviewWithOnboardingAPI();
	};

	return (
		<div className="give-obw-introduction">
			<Card>
				<div className="give-obw-introduction__content">
					<h1 className="give-obw-introduction__heading">
						{ __( 'Welcome To', 'give' ) }<span className="screen-reader-text">{ __( 'GiveWP', 'give' ) }</span>
					</h1>
					<GiveLogo />
					<p>
						{ __( 'Get started quickly with our Onboarding Wizard and begin accepting donations on your website in minutes.', 'give' ) }
					</p>
					<ContinueButton clickCallback={ () => onStartSetup() } label={ __( 'Start Setup', 'give' ) } testId="intro-continue-button" />
				</div>
			</Card>
			<DismissLink />
		</div>
	);
};

export default Introduction;
