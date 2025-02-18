import {__} from '@wordpress/i18n';
import {useSelect} from '@wordpress/data';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {BlockEditProps} from '@wordpress/blocks';
import {ExternalLink, PanelBody, TextControl} from '@wordpress/components';
import useCampaign from '../shared/hooks/useCampaign';
import CampaignGoalApp from './app/index';
import CampaignSelector from '../shared/components/CampaignSelector';
import {getGoalDescription} from './utils';

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
            <CampaignSelector
                campaignId={attributes.campaignId}
                handleSelect={(campaignId: number) => setAttributes({campaignId})}
            >
                <CampaignGoalApp campaign={campaign} />
            </CampaignSelector>

            {campaign?.id && (
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
