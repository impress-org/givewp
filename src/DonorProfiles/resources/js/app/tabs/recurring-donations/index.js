// Internal dependencies
import Content from './content';

export const registerRecurringDonationsTab = () => window.giveDonorProfile.utils.registerTab( {
	label: 'Recurring Donations',
	icon: 'sync',
	slug: 'recurring-donations',
	content: Content,
} );
