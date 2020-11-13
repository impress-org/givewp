// Internal dependencies
import Content from './content';
import DashboardContent from './dashboard-content';

export const registerDonationHistoryTab = () => {
	window.giveDonorProfile.utils.registerTab( {
		label: 'Donation History',
		icon: 'calendar-alt',
		slug: 'donation-history',
		content: Content,
		dashboardContent: DashboardContent,
	} );
};
