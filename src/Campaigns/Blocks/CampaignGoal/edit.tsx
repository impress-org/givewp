import {__} from '@wordpress/i18n';
import {useSelect} from '@wordpress/data';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import {BlockEditProps} from '@wordpress/blocks';
import {ExternalLink, PanelBody, TextControl} from '@wordpress/components';
import useCampaign from '../shared/hooks/useCampaign';
import {CampaignSelector} from '../shared/components/CampaignSelector';

import './styles.scss';

const getGoalDescription = (goalType: string) => {
    switch (goalType) {
        case 'amount':
            return __('Amount raised', 'give');
        case 'donations':
            return __('Number of donations', 'give');
        case 'donors':
            return __('Number of donors', 'give');
        case 'amountFromSubscriptions':
            return __('Recurring amount raised', 'give');
        case 'subscriptions':
            return __('Number of recurring donations', 'give');
        case 'donorsFromSubscriptions':
            return __('Number of recurring donors', 'give');
    }
}

/**
 * @unreleased
 */
export default function Edit({attributes, setAttributes}: BlockEditProps<{
    campaignId: number;
    goalType: string;
}>) {
    const {campaign, hasResolved} = useCampaign(attributes.campaignId);

    const blockProps = useBlockProps();

    const adminBaseUrl = useSelect(
        // @ts-ignore
        (select) => select('core').getSite()?.url + '/wp-admin/edit.php?post_type=give_forms&page=give-campaigns',
        []
    );

    if (!hasResolved) {
        return null;
    }

    return (
        <div {...blockProps}>
            <CampaignSelector attributes={attributes} setAttributes={setAttributes}>
                <ServerSideRender block="givewp/campaign-goal" attributes={attributes} />
            </CampaignSelector>

            {hasResolved && campaign?.id && (
                <InspectorControls>
                    <PanelBody title={__('Settings', 'give')} initialOpen={true}>
                        <TextControl value={getGoalDescription(campaign.goalType)} onChange={null} disabled={true} />
                        <ExternalLink
                            href={`${adminBaseUrl}&id=${attributes.campaignId}&tab=settings#campaign-goal`}
                            title={__('Edit campaign goal settings', 'give')}
                        >
                            {__('Edit campaign goal settings', 'give')}
                        </ExternalLink>
                    </PanelBody>
                </InspectorControls>
            )}
        </div>
    );
}
