// Internal dependencies
import Content from './content';

export const registerDashboardTab = () => window.giveDonorDashboard.utils.registerTab( {
	label: 'Dashboard',
	icon: 'home',
	slug: 'dashboard',
	content: Content,
} );
