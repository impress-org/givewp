import { __ } from '@wordpress/i18n';
;

// Internal dependencies
import Content from './content';
import DashboardContent from './dashboard-content';
import { fetchDonationsDataFromAPI } from './utils';

export const registerDonationHistoryTab = () => {
	fetchDonationsDataFromAPI();

	window.giveDonorDashboard.utils.registerTab( {
		label: __( 'Donation History', 'give' ),
		icon: 'calendar-alt',
		slug: 'donation-history',
		content: Content,
		dashboardContent: DashboardContent,
	} );
};
