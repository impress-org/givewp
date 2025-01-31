import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {__} from '@wordpress/i18n';
import {useSelect} from '@wordpress/data';
import {external} from '@wordpress/icons';
import {BaseControl, Icon, PanelBody, Placeholder, ResizableBox, TextareaControl} from '@wordpress/components';
import {BlockEditProps} from '@wordpress/blocks';
import {CampaignSelector} from '../shared/components/CampaignSelector';
import useCampaign from '../shared/hooks/useCampaign';
import {GalleryIcon} from "./Icon";

import './editor.scss';

interface EditProps extends BlockEditProps<{
    campaignId: number;
    alt: string;
    width: number;
    height: number;
    align: string;
    duotone: any;
}> {
    toggleSelection: (isSelected: boolean) => void;
}

export default function Edit({attributes, setAttributes, toggleSelection}: EditProps) {
    const blockProps = useBlockProps();
    const {campaign, hasResolved} = useCampaign(attributes.campaignId);

    const adminBaseUrl = useSelect(
        // @ts-ignore
        (select) => select('core').getSite()?.url + '/wp-admin/edit.php?post_type=give_forms&page=give-campaigns',
        []
    );
    const editCampaignUrl = `${adminBaseUrl}&id=${attributes.campaignId}&tab=settings`;

    const handleResizeStop = (event: MouseEvent | TouchEvent, direction, refToElement: HTMLDivElement, delta: {
        height: number,
        width: number
    }) => {
        setAttributes({
            height: attributes.height + delta.height,
            width: attributes.width + delta.width,
        });
        toggleSelection(true);
    };

    const isSizeAligned = attributes.align === 'full' || attributes.align === 'wide';

    return (
        <figure {...blockProps}>
            <CampaignSelector attributes={attributes} setAttributes={setAttributes}>
                {hasResolved && !campaign?.image && (
                    <Placeholder
                        icon={<GalleryIcon />}
                        label={__('Campaign Cover Image', 'give')}
                        instructions={__('Upload a cover image for your campaign.', 'give')}
                    />

                )}

                {hasResolved && campaign?.image &&
                    (!isSizeAligned ? (
                        <ResizableBox
                            size={{
                                width: attributes.width,
                                height: attributes.height,
                            }}
                            /* max-width of the block editor with alignment='none' */
                            maxWidth={645}
                            style={{
                                position: 'relative',
                                userSelect: 'auto',
                                display: 'block',
                                boxSizing: 'border-box',
                                width: 'auto',
                                height: 'auto',
                            }}
                            onResizeStart={() => {
                                toggleSelection(false);
                            }}
                            onResizeStop={handleResizeStop}
                            enable={{
                                bottom: true,
                                right: true,
                                bottomRight: false,
                                top: false,
                                left: false,
                                topLeft: false,
                                topRight: true,
                                bottomLeft: false,
                            }}
                        >
                            <img
                                className={'givewp-campaign-cover-block-preview__image'}
                                src={campaign?.image}
                                alt={attributes.alt ?? __('Campaign Image', 'give')}
                                style={{width: '100%', height: '100%'}}
                            />
                        </ResizableBox>
                    ) : (
                        <img
                            className={'givewp-campaign-cover-block-preview__image'}
                            src={campaign?.image}
                            alt={attributes.alt ?? __('Campaign Image', 'give')}
                            style={{width: '100%', height: '100%'}}
                        />
                    ))}
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
        </figure>
    );
}
