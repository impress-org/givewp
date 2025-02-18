import {__} from '@wordpress/i18n';
import {PanelBody, SelectControl} from '@wordpress/components';
import {InspectorControls} from '@wordpress/block-editor';
import {Campaign} from '@givewp/campaigns/admin/components/types';

type CampaignDropdownProps = {
    campaignId: number;
    campaigns: Campaign[],
    hasResolved: boolean;
    handleSelect: (id: number) => void;
}

export default function CampaignDropdown({campaignId, campaigns, hasResolved, handleSelect}: CampaignDropdownProps) {
    const options = (() => {
        if (!hasResolved) {
            return [{label: __('Loading...', 'give'), value: ''}];
        }

        if (campaigns.length) {
            const campaignOptions = campaigns.map((campaign) => ({
                label: campaign.title,
                value: campaign.id.toString(),
            }));

            return [{label: __('Select...', 'give'), value: ''}, ...campaignOptions];
        }

        return [{label: __('No campaigns found.', 'give'), value: ''}];
    })();

    return (
        <InspectorControls>
            <PanelBody title={__('Campaign', 'give')} initialOpen={true}>
                <SelectControl
                    label={__('Select a Campaign', 'give')}
                    value={campaignId?.toString()}
                    options={options}
                    onChange={(newValue: string) => handleSelect(parseInt(newValue))}
                />
            </PanelBody>
        </InspectorControls>
    );
}
