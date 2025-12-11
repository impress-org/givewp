import { __ } from '@wordpress/i18n';
import OverviewTab from './Overview';
import ProfileTab from './Profile';
import { Tab } from '@givewp/components/AdminDetailsPage/types';

/**
 * @since 4.4.0
 */
const donorDetailsTabs: Tab[] = [
    {
        id: 'overview',
        title: __('Overview', 'give'),
        content: OverviewTab,
    },
    {
        id: 'profile',
        title: __('Profile', 'give'),
        content: ProfileTab,
    },
];

export default donorDetailsTabs;
