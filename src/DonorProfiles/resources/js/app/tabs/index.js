// Internal dependencies
import { registerAnnualReceiptsTab } from './annual-receipts';
import { registerDashboardTab } from './dashboard';
import { registerDonationHistoryTab } from './donation-history';
import { registerEditProfileTab } from './edit-profile';
import { registerRecurringDonationsTab } from './recurring-donations';

export const registerDefaultTabs = () => {
	registerDashboardTab();
	registerDonationHistoryTab();
	registerRecurringDonationsTab();
	registerAnnualReceiptsTab();
	registerEditProfileTab();
};
