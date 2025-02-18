import {PanelBody, SelectControl} from '@wordpress/components';
import {InspectorControls} from '@wordpress/block-editor';
import {__} from '@wordpress/i18n';

export default function CampaignDropdown({campaignId, campaigns, hasResolved, handleSelect}) {
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
                    value={campaignId || ''}
                    options={options}
                    disabled={options.length === 1}
                    onChange={(newValue: string) => handleSelect({campaignId: newValue ? parseInt(newValue) : null})}
                />
            </PanelBody>
        </InspectorControls>
    );
}
