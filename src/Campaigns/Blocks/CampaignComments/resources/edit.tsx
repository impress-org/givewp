import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {BlockEditProps} from '@wordpress/blocks';
import {PanelBody, TextControl, ToggleControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import CampaignComments from './shared/components/CampaignComments';
import useCampaign from '../../shared/hooks/useCampaign';
import {CampaignSelector} from '../../shared/components/CampaignSelector';
import {useEffect} from 'react';
import {Attributes} from './types';

export default function Edit({attributes, setAttributes, clientId}: BlockEditProps<Attributes>) {
    const blockProps = useBlockProps();
    const {campaign, hasResolved} = useCampaign(attributes?.campaignId);

    useEffect(() => {
        if (!attributes.blockId) {
            setAttributes({blockId: clientId});
        }
    }, []);

    return (
        <figure {...blockProps}>
            <CampaignSelector attributes={attributes} setAttributes={setAttributes}>
                <CampaignComments attributes={attributes} />
            </CampaignSelector>

            {hasResolved && campaign?.id && (
                <InspectorControls>
                    <PanelBody title={__('Display Elements', 'give')} initialOpen={true}>
                        <TextControl
                            label={__('Title', 'give')}
                            value={attributes.title}
                            onChange={(value: string) => setAttributes({title: value})}
                        />
                        <ToggleControl
                            label={__('Show Anonymous', 'give')}
                            checked={attributes.showAnonymous}
                            onChange={(value: boolean) => setAttributes({showAnonymous: value})}
                        />
                        <ToggleControl
                            label={__('Show Avatar', 'give')}
                            checked={attributes.showAvatar}
                            onChange={(value: boolean) => setAttributes({showAvatar: value})}
                        />
                        <ToggleControl
                            label={__('Show Date', 'give')}
                            checked={attributes.showDate}
                            onChange={(value: boolean) => setAttributes({showDate: value})}
                        />
                        <ToggleControl
                            label={__('Show Name', 'give')}
                            checked={attributes.showName}
                            onChange={(value: boolean) => setAttributes({showName: value})}
                        />
                    </PanelBody>
                    <PanelBody title={__('Comment Settings', 'give')} initialOpen={true}>
                        <TextControl
                            label={__('Comment Length', 'give')}
                            help={__(
                                'Limits the amount of characters to be displayed on donations with comments.',
                                'give'
                            )}
                            value={String(attributes.commentLength)}
                            onChange={(value: string) => setAttributes({commentLength: Number(value)})}
                        />
                        <TextControl
                            label={__('Read More Text', 'give')}
                            value={attributes.readMoreText}
                            onChange={(value: string) => setAttributes({readMoreText: value})}
                        />
                        <TextControl
                            label={__('Comments Per Page', 'give')}
                            help={__('Set the number of comments to be displayed on the first page load.', 'give')}
                            value={String(attributes.commentsPerPage)}
                            onChange={(value: string) => setAttributes({commentsPerPage: Number(value)})}
                        />
                    </PanelBody>
                </InspectorControls>
            )}
        </figure>
    );
}
