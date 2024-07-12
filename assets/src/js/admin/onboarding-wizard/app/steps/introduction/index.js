// Import vendor dependencies
import { __ } from '@wordpress/i18n';

// Import store dependencies
import {useStoreValue} from "../../store";
import {setFormId} from "../../store/actions";

// Import styles
import './style.scss';

// Import utilities
import { generateFormPreviewWithOnboardingAPI } from '../../../utils';

import Card from '../../../components/card';
import GiveLogo from '../../../components/give-logo';
import ContinueButton from '../../../components/continue-button';
import DismissLink from '../../../components/dismiss-link';

const Introduction = () => {
    const [ {}, dispatch ] = useStoreValue();

    const onStartSetup = async() => {
	    const formId = await generateFormPreviewWithOnboardingAPI();
        dispatch(setFormId(formId));
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
						{ __( 'You\'re only minutes away from accepting donations on your website! Use the Onboarding Wizard if this is your first time using GiveWP.', 'give' ) }
					</p>
					<ContinueButton clickCallback={ () => onStartSetup() } label={ __( 'Start Setup', 'give' ) } testId="intro-continue-button" />
				</div>
			</Card>
			<DismissLink />
		</div>
	);
};

export default Introduction;
