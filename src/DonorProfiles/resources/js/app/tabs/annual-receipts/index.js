// Internal dependencies
import Content from './content';

export const registerAnnualReceiptsTab = () => window.giveDonorProfile.utils.registerTab( {
	label: 'Annual Receipts',
	icon: 'receipt',
	slug: 'annual-receipts',
	content: Content,
} );
