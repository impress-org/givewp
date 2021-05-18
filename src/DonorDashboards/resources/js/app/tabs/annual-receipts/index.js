// Internal dependencies
import Content from './content';
import { __ } from '@wordpress/i18n';

export const registerAnnualReceiptsTab = () => window.giveDonorDashboard.utils.registerTab( {
	label: __( 'Annual Receipts', 'give' ),
	icon: 'receipt',
	slug: 'annual-receipts',
	content: Content,
} );
