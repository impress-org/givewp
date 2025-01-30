import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {__} from '@wordpress/i18n';
import {useSelect} from '@wordpress/data';
import {external} from '@wordpress/icons';
import {BaseControl, Icon, PanelBody, Placeholder, TextareaControl} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import {BlockEditProps} from '@wordpress/blocks';

import {CampaignSelector} from '../shared/components/CampaignSelector';
import useCampaign from '../shared/hooks/useCampaign';
import './editor.scss';
import {GalleryIcon} from './Icon';

export default function Edit({
    attributes,
    setAttributes,
}: BlockEditProps<{
    campaignId: number;
    alt: string;
}>) {
    const blockProps = useBlockProps();
    const {campaign, hasResolved} = useCampaign(attributes.campaignId);

    const adminBaseUrl = useSelect(
        // @ts-ignore
        (select) => select('core').getSite()?.url + '/wp-admin/edit.php?post_type=give_forms&page=give-campaigns',
        []
    );

    const editCampaignUrl = `${adminBaseUrl}&id=${attributes.campaignId}&tab=settings`;

    return (
        <div {...blockProps}>
            <CampaignSelector attributes={attributes} setAttributes={setAttributes}>
                {campaign?.image ? (
                    <ServerSideRender block="givewp/campaign-cover-block" attributes={attributes} />
                ) : (
                    <Placeholder
                        icon={<GalleryIcon />}
                        label={__('Campaign Cover Image', 'give')}
                        instructions={__('Upload a cover image for your campaign.', 'give')}
                    />
                )}
            </CampaignSelector>

            {hasResolved && campaign && (
                <InspectorControls>
                    <PanelBody title="Settings" initialOpen={true}>
                        <BaseControl label={__('Cover', 'give')} id="givewp-campaign-cover-block__title-field">
                            {campaign?.image && (
                                <img
                                    className={'givewp-campaign-cover-block__image'}
                                    src={campaign?.image}
                                    alt={attributes.alt ?? __('Campaign Cover image', 'give')}
                                />
                            )}
                            <p className={'givewp-campaign-cover-block__help-text'}>
                                {__('Shows the cover image of the campaign.', 'give')}
                            </p>
                            <a
                                href={editCampaignUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="givewp-campaign-cover-block__edit-campaign-link"
                                aria-label={__('Edit campaign settings in a new tab', 'give')}
                            >
                                {__('Change campaign cover', 'give')}
                                <Icon icon={external} />
                            </a>
                        </BaseControl>
                        <TextareaControl
                            label={__('Alternative text', 'give')}
                            value={attributes.alt}
                            onChange={(value: string) => setAttributes({alt: value})}
                        />
                    </PanelBody>
                </InspectorControls>
            )}
        </div>
    );
}
