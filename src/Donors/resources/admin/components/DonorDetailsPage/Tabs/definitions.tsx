import { __ } from '@wordpress/i18n';
import OverviewTab from './Overview';
import ProfileTab from './Profile';
import { DonorDetailsTab } from '../types';

const donorDetailsTabs: DonorDetailsTab[] = [
    {
        id: 'overview',
        title: __('Overview', 'give'),
        content: () => <OverviewTab />,
    },
    {
        id: 'profile',
        title: __('Profile', 'give'),
        content: () => <ProfileTab />,
    },
];

export default donorDetailsTabs;
