import {CampaignDetailsTab} from './types';
import {__} from '@wordpress/i18n';
import {getGiveCampaignDetailsWindowData} from './index';
import SettingsTab from './Tabs/Settings'

const {campaign} = getGiveCampaignDetailsWindowData();

const campaignDetailsTabs: CampaignDetailsTab[] = [
    {
        id: 'overview',
        title: __('Overview', 'give'),
        content: () => (
            <>
                <p>Overview component goes here...</p>
                <ul>
                    {Object.entries(campaign.properties).map(([property, value], index) => (
                        <li key={index}>
                            <span>
                                <strong>{property}:</strong> {String(value)}
                            </span>
                        </li>
                    ))}
                </ul>
            </>
        ),
    },
    {
        id: 'settings',
        title: __('Settings', 'give'),
        content: () => <SettingsTab defaultValues={...campaign.properties} />,
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
