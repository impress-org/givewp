export const getWindowData = ( value ) => {
	const data = window.giveOnboardingWizardData;
	return data[ value ];
};

export const redirectToSetupPage = () => {
	window.location.href = getWindowData( 'setupUrl' );
};
