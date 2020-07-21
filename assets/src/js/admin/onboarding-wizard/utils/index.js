export const getWindowData = ( value ) => {
	const data = window.giveOnboardingWizardData;
	return data[ value ];
};
