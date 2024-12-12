import {PanelBody, SelectControl} from '@wordpress/components';
import {InspectorControls} from '@wordpress/block-editor';
import useCampaigns from '../hooks/useCampaigns';
import {Campaign} from '@givewp/campaigns/admin/components/types';
import {__} from '@wordpress/i18n';

export default function CampaignDropdown({campaignId, setAttributes, placement = 'sidebar'}) {
    const {campaigns, hasResolved} = useCampaigns();

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

    const dropdown = (
        <SelectControl
            label={__('Select a Campaign', 'give')}
            value={campaignId || ''}
            options={options}
            disabled={options.length === 1}
            onChange={(newValue) => setAttributes({campaignId: newValue ? parseInt(newValue) : null})}
        />
    );

    if (placement === 'sidebar') {
        return (
            <InspectorControls>
                <PanelBody title={__('Campaign', 'give')} initialOpen={true}>
                    {dropdown}
                </PanelBody>
            </InspectorControls>
        );
    }

    return dropdown;
}
