import {useState} from 'react';
import {__} from '@wordpress/i18n';
import {Campaign} from '@givewp/campaigns/admin/components/types';
import ReactSelect from 'react-select';
import {reactSelectStyles, reactSelectThemeStyles} from './reactSelectStyles';
import logo from './images/givewp-logo.svg';

import './styles.scss';

type CampaignSelectorProps = {
    hasResolved: boolean;
    campaigns: Campaign[];
    handleSelect: (id: number) => void;
}

/**
 * @since 4.0.0
 */
export default ({campaigns, hasResolved, handleSelect}: CampaignSelectorProps) => {
    const [selectedCampaign, setSelectedCampaign] = useState<number>(null);

    const campaignOptions = (() => {
        if (!hasResolved) {
            return [{label: __('Loading...', 'give'), value: ''}];
        }

        if (campaigns.length) {
            const campaignOptions = campaigns.map((campaign) => ({
                label: `${campaign.title} ${campaign.status === 'draft' ? `(${__('Draft', 'give')})` : ''}`.trim(),
                value: campaign.id,
            }));

            return [{label: __('Select a campaign', 'give'), value: ''}, ...campaignOptions];
        }

        return [{label: __('No campaigns found.', 'give'), value: ''}];
    })();

    const campaign = campaignOptions.find(option => option.value === selectedCampaign);

    return (
        <div className="givewp-campaign-selector">
            <img className="givewp-campaign-selector__logo" src={logo} alt="givewp-logo" />
            <div className="givewp-campaign-selector__select">
                <label htmlFor="campaignId" className="givewp-campaign-selector__label">
                    {__('Choose a campaign', 'give')}
                </label>

                <ReactSelect
                    name="campaignId"
                    inputId="campaignId"
                    value={campaign}
                    //@ts-ignore
                    onChange={(option) => setSelectedCampaign(option?.value)}
                    noOptionsMessage={() => <p>{__('No campaigns were found.', 'give')}</p>}
                    //@ts-ignore
                    options={campaignOptions}
                    loadingMessage={() => <>{__('Loading Campaigns...', 'give')}</>}
                    isLoading={!hasResolved}
                    theme={reactSelectThemeStyles}
                    styles={reactSelectStyles}
                />
            </div>

            <button
                className="givewp-campaign-selector__submit"
                type="button"
                disabled={!selectedCampaign}
                onClick={() => {
                    handleSelect(selectedCampaign);
                }}
            >
                {__('Confirm', 'give')}
            </button>
        </div>
    );
}
