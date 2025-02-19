import {__} from '@wordpress/i18n';
import {PanelBody, SelectControl} from '@wordpress/components';
import {InspectorControls} from '@wordpress/block-editor';
import {Campaign} from '@givewp/campaigns/admin/components/types';
import {useSelect} from "@wordpress/data";

type CampaignDropdownProps = {
    campaignId: number;
    campaigns: Campaign[],
    hasResolved: boolean;
    handleSelect: (id: number) => void;
    inspectorControls?: JSX.Element | JSX.Element[];
}

export default function CampaignDropdown({campaignId, campaigns, hasResolved, handleSelect, inspectorControls = null}: CampaignDropdownProps) {

    const adminBaseUrl = useSelect(
        // @ts-ignore
        (select) => select('core').getSite()?.url + '/wp-admin/edit.php?post_type=give_forms&page=give-campaigns',
        []
    );

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
                    help={
                        <>
                            {__('Select a campaign to display.', 'give') +  ` `}
                            {campaignId && (
                                <a
                                    href={`${adminBaseUrl}&id=${campaignId}&tab=settings`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="givewp-campaign-cover-block__edit-campaign-link"
                                    aria-label={__('Edit campaign settings in a new tab', 'give')}
                                >
                                    {__('Edit campaign', 'give')}
                                </a>
                            )}
                        </>
                    }
                />
                {inspectorControls}
            </PanelBody>
        </InspectorControls>
    );
}
