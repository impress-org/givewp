import {CampaignDetailsTab} from '../types';
import {__} from '@wordpress/i18n';
import OverviewTab from './Overview';
import SettingsTab from './Settings';
import FormsTab from './Forms';

const campaignDetailsTabs: CampaignDetailsTab[] = [
    {
        id: 'overview',
        title: __('Overview', 'give'),
        content: () => <OverviewTab />,
    },
    {
        id: 'settings',
        title: __('Settings', 'give'),
        content: () => <SettingsTab />,
    },
    {
        id: 'forms',
        title: __('Forms', 'give'),
        content: () => <FormsTab />,
        fullwidth: true,
    },
];

export default campaignDetailsTabs;
