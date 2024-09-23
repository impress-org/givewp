import {CampaignDetailsTab} from '../types';
import {__} from '@wordpress/i18n';
import OverviewTab from './Overview';
import SettingsTab from './Settings';

const urlParams = new URLSearchParams(window.location.search);

const campaignDetailsTabs: CampaignDetailsTab[] = [
    {
        id: 'overview',
        title: __('Overview', 'give'),
        content: () => <OverviewTab campaignId={urlParams.get('id')} />,
    },
    {
        id: 'settings',
        title: __('Settings', 'give'),
        content: () => <SettingsTab campaignId={urlParams.get('id')} />,
    },
    {
        id: 'forms',
        title: __('Forms', 'give'),
        content: () => (
            <>
                <p>Forms list table goes here...</p>
            </>
        ),
    },
];

export default campaignDetailsTabs;
