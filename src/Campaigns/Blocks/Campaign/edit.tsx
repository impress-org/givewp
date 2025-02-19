import {__} from '@wordpress/i18n';
import {CSSProperties, useState} from 'react';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {BlockEditProps} from '@wordpress/blocks';
import {PanelBody, ToggleControl} from '@wordpress/components';
import {CampaignBlockType} from './types';
import CampaignSelector from '../shared/components/CampaignSelector';
import useCampaign from '../shared/hooks/useCampaign';
import CampaignCard from '../shared/components/CampaignCard';
import {BlockNotice} from '@givewp/form-builder-library';
import {getCampaignOptionsWindowData} from '@givewp/campaigns/utils';


const styles = {
    title: {
        fontWeight: 600
    },
    notice: {
        position: 'relative',
        display: 'flex',
        flexDirection: 'column',
        gap: 8,
        padding: 16,
        borderRadius: 2,
        color: '#0e0e0e',
        background: '#f2f2f2',
        fontSize: 12,
        lineHeight: 1.33,
    },
    close: {
        position: 'absolute',
        cursor: 'pointer',
        right: 16,
        top: 16,
    }
} as CSSProperties;

const CloseIcon = () => (
    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
            d="M11.805 5.138a.667.667 0 1 0-.943-.943L8 7.057 5.138 4.195a.667.667 0 1 0-.943.943L7.057 8l-2.862 2.862a.667.667 0 1 0 .943.943L8 8.943l2.862 2.862a.667.667 0 1 0 .943-.943L8.943 8l2.862-2.862z"
            fill="#0E0E0E" />
    </svg>
)

export default function Edit({attributes, setAttributes}: BlockEditProps<CampaignBlockType>) {
    const blockProps = useBlockProps();
    const campaignWindowData = getCampaignOptionsWindowData();
    const [showNotification, setShowNotification] = useState(campaignWindowData.admin.showCampaignInteractionNotice);
    const {campaign, hasResolved} = useCampaign(attributes.campaignId);

    const Notices = () => {
        if (!attributes.campaignId) {
            return null;
        }

        if (campaign.enableCampaignPage) {
            if (!showNotification) {
                return null;
            }

            return (
                <p style={styles['notice']}>
                    <span
                        style={styles['close']}
                        onClick={() => {
                            fetch(campaignWindowData.adminUrl + '/admin-ajax.php?action=givewp_campaign_interaction_notice', {method: 'POST'})
                                .then(() => setShowNotification(false))
                        }}>
                        <CloseIcon />
                    </span>
                    <span style={styles['title']}>
                        {__('Campaign interaction', 'give ')}
                    </span>
                    <span>
                        {__('Users will be redirected to campaign page.', 'give')}
                    </span>
                </p>
            )
        }

        return (
            <BlockNotice
                title={__('Campaign page has been disabled for this campaign.', 'give ')}
                description={__('For this campaign block to work properly, enable the campaign page for this campaign.', 'give')}
                anchorText={__('Enable campaign page', 'give')}
                href={`${campaignWindowData.campaignsAdminUrl}&id=${attributes.campaignId}&tab=settings`}
            />
        )
    };

    return (
        <div {...blockProps}>
            {hasResolved && (
                <>
                    <CampaignSelector
                        campaignId={attributes.campaignId}
                        handleSelect={(campaignId: number) => setAttributes({campaignId})}
                        showInspectorControl={true}
                        inspectorControls={<Notices />}
                    >
                        <CampaignCard
                            campaign={campaign}
                            showImage={attributes.showImage}
                            showDescription={attributes.showDescription}
                            showGoal={attributes.showGoal}
                        />
                    </CampaignSelector>

                    <InspectorControls>
                        <PanelBody title={__('Display Elements', 'give')} initialOpen={true}>
                            <ToggleControl
                                label={__('Show campaign image', 'give')}
                                checked={attributes.showImage}
                                onChange={(showImage) => setAttributes({showImage})}
                            />
                            <ToggleControl
                                label={__('Show description', 'give')}
                                checked={attributes.showDescription}
                                onChange={(showDescription) => setAttributes({showDescription})}
                            />
                            <ToggleControl
                                label={__('Show goal', 'give')}
                                checked={attributes.showGoal}
                                onChange={(showGoal) => setAttributes({showGoal})}
                            />
                        </PanelBody>
                    </InspectorControls>
                </>
            )}
        </div>
    )
}
