import {__} from '@wordpress/i18n';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {BlockEditProps} from '@wordpress/blocks';
import {PanelBody, ToggleControl} from '@wordpress/components';
import {CampaignBlockType} from './types';
import CampaignSelector from "../shared/components/CampaignSelector";
import useCampaign from "../shared/hooks/useCampaign";
import CampaignCard from "../shared/components/CampaignCard";

export default function Edit({attributes, setAttributes}: BlockEditProps<CampaignBlockType>) {
    const blockProps = useBlockProps();
    const {campaign, hasResolved} = useCampaign(attributes.campaignId);

    return (
        <div {...blockProps}>
            {hasResolved && (
                <>
                    <CampaignSelector
                        showInCampaignContextOnly={false}
                        campaignId={attributes.campaignId}
                        handleSelect={(campaignId: number) => setAttributes({campaignId})}
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
