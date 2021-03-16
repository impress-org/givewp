// Internal dependencies
import { registerAnnualReceiptsTab } from './annual-receipts';
import { registerDashboardTab } from './dashboard';
import { registerDonationHistoryTab } from './donation-history';
import { registerEditProfileTab } from './edit-profile';
import { registerRecurringDonationsTab } from './recurring-donations';
import { getWindowData } from '../utils';

export const registerDefaultTabs = () => {
	// Dashboard Tab should always register
	registerDashboardTab();

	const tabRegistrationMap = {
		'donation-history': registerDonationHistoryTab,
		'annual-receipts': registerAnnualReceiptsTab,
		'recurring-donations': registerRecurringDonationsTab,
		'edit-profile': registerEditProfileTab,
	};

	const registeredTabs = getWindowData( 'registeredTabs' );

	registeredTabs.forEach( ( tab ) => {
		tabRegistrationMap[ tab ]();
	} );
};
