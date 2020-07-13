// Import styles
import './style.scss';

const { __ } = wp.i18n;

/**
 * Onboarding Wizard app component
 *
 * @since 2.8.0
 * @returns {array} Array of React elements, comprising the Onboarding Wizard app
 */
const App = () => {
	return (
		<div className="give-obw">
			<div className="give-obw-step">
				<h2>
					{ __( 'Welcome To', 'give' ) }
				</h2>
				<div className="give-obw-logo">{ __( 'GiveWP', 'give' ) }</div>
				<p>
					{ __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed in mi a leo convallis consequat. Sed ornare tellus vel justo porttitor, eu bibendum lorem vulputate.', 'give' ) }
				</p>
				<div className="give-obw-buttons">
					<a className="give-obw-button give-obw-button--primary" href="#">{ __( 'Get Started', 'give' ) }</a>
					<a className="give-obw-button" href={ window.giveOnboardingWizardData.setupUrl }>{ __( 'Not right now.', 'give' ) }</a>
				</div>
			</div>
		</div>
	);
};
export default App;
