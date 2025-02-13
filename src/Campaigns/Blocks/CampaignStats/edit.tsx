import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {__} from '@wordpress/i18n';
import {BlockEditProps} from '@wordpress/blocks';
import {PanelBody, SelectControl} from '@wordpress/components';
import {CampaignSelector} from '../shared/components/CampaignSelector';
import ServerSideRender from '@wordpress/server-side-render';
import useCampaign from '../shared/hooks/useCampaign';

import './styles.scss';

type statisticType = 'top-donation' | 'average-donation';

export default function Edit({
    attributes,
    setAttributes,
}: BlockEditProps<{
    campaignId: number;
    statistic: statisticType;
}>) {
    const blockProps = useBlockProps();
    const {campaign, hasResolved} = useCampaign(attributes?.campaignId);

    return (
        <figure {...blockProps}>
            <CampaignSelector attributes={attributes} setAttributes={setAttributes}>
                <ServerSideRender block="givewp/campaign-stats-block" attributes={attributes} />
            </CampaignSelector>

            {hasResolved && campaign?.id && (
                <InspectorControls>
                    <PanelBody title="Settings" initialOpen={true}>
                        <SelectControl
                            label={__('Type', 'give')}
                            help={__('Displays the top donation of the selected campaign.', 'give')}
                            value={attributes.statistic}
                            options={[
                                {value: 'top-donation', label: __('Top Donation', 'give')},
                                {value: 'average-donation', label: __('Average Donation', 'give')},
                            ]}
                            onChange={(value: statisticType) => setAttributes({statistic: value})}
                        />
                    </PanelBody>
                </InspectorControls>
            )}
        </figure>
    );
}
