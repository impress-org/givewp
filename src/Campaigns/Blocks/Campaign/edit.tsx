import {__} from '@wordpress/i18n';
import {CSSProperties} from 'react';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {BlockEditProps} from '@wordpress/blocks';
import {PanelBody, ToggleControl} from '@wordpress/components';
import {CampaignBlockType} from './types';
import CampaignSelector from '../shared/components/CampaignSelector';
import useCampaign from '../shared/hooks/useCampaign';
import CampaignCard from '../shared/components/CampaignCard';
import {useSelect} from '@wordpress/data';

const styles = {
    shared: {
        position: 'relative',
        display: 'flex',
        flexDirection: 'column',
        gap: 8,
        padding: 16,
        borderRadius: 2,
        color: '#0e0e0e'
    },
    title: {
        fontWeight: 600
    },
    link: {
        color: '#0e0e0e'
    },
    enabled: {
        background: '#f2f2f2',
        fontSize: 12,
        lineHeight: 1.33,
    },
    disabled: {
        background: '#fffaf2',
        borderLeft: '1px solid #f29718',
    },
    close: {
        position: 'absolute',
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
    const {campaign, hasResolved} = useCampaign(attributes.campaignId);
    const adminBaseUrl = useSelect(
        // @ts-ignore
        (select) => select('core').getSite()?.url + '/wp-admin/edit.php?post_type=give_forms&page=give-campaigns',
        []
    );

    return (
        <div {...blockProps}>
            {hasResolved && (
                <>
                    <CampaignSelector
                        campaignId={attributes.campaignId}
                        handleSelect={(campaignId: number) => setAttributes({campaignId})}
                        showInspectorControl={true}
                        inspectorControls={
                            <>
                                {attributes.campaignId && (
                                    campaign.enableCampaignPage
                                        ? (
                                            <p style={{...styles['shared'], ...styles['enabled']}}>
                                            <span style={styles['close']}>
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
                                        : (
                                            <p style={{...styles['shared'], ...styles['disabled']}}>
                                            <span style={styles['title']}>
                                                {__('Campaign page has been disabled for this campaign.', 'give ')}
                                            </span>
                                                <span>
                                                {__('For this campaign block to work properly, enable the campaign page for this campaign.', 'give')}
                                            </span>
                                                <a
                                                    style={styles['link']}
                                                    href={`${adminBaseUrl}&id=${attributes.campaignId}&tab=settings`}
                                                    rel="noopener noreferrer"
                                                    aria-label={__('Edit campaign settings in a new tab', 'give')}
                                                >
                                                    {__('Enable campaign page', 'give')}
                                                </a>
                                            </p>
                                        )
                                )}
                            </>
                        }
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
