// Onboarding Wizard App

import './style.scss';

const App = () => {
	return (
		<div className="give-obw">
			<div className="give-obw-step">
				<h2>
					Welcome To
				</h2>
				<div className="give-obw-logo">GiveWP</div>
				<p>
					Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed in mi a leo convallis consequat. Sed ornare tellus vel justo porttitor, eu bibendum lorem vulputate.
				</p>
				<div className="give-obw-buttons">
					<a className="give-obw-button give-obw-button--primary" href="#">Get Started</a>
					<a className="give-obw-button" href={ window.giveOnboardingWizardData.setupUrl }>Not right now.</a>
				</div>
			</div>
		</div>
	);
};
export default App;
