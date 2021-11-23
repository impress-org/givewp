// Internal dependencies
import Content from './content';
import {__} from '@wordpress/i18n';

export const registerDashboardTab = () =>
    window.giveDonorDashboard.utils.registerTab({
        label: __('Dashboard', 'give'),
        icon: 'home',
        slug: 'dashboard',
        content: Content,
    });
