import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {BlockEditProps} from '@wordpress/blocks';
import {PanelBody, SelectControl, TextControl, TextareaControl, ToggleControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

const DEFAULT_NOTICE_SHORT_TEXT = 'Do not affect stats';
const DEFAULT_NOTICE_CTA = 'Learn more';
const DEFAULT_NOTICE_LONG_TEXT =
    'Crypto and stock donations are processed and counted independently from regular donations. Campaign statistics (amount raised, top donors, recent donations, etc.) only include donations made through the standard donation form. They do not include crypto or stock donations made via The Giving Block.';

type BlockAttributes = {
    displayType: string;
    popupButtonText: string;
    popupButtonNoticeEnable?: boolean;
    popupButtonNoticeShortText?: string;
    popupButtonNoticeLongText?: string;
    popupButtonNoticeShortCta?: string;
};

/**
 * Block edit component for The Giving Block donation form.
 *
 * @unreleased
 */
export default function Edit({attributes, setAttributes}: BlockEditProps<BlockAttributes>) {
    const blockProps = useBlockProps();
    const {
        displayType,
        popupButtonText,
        popupButtonNoticeEnable,
        popupButtonNoticeShortText,
        popupButtonNoticeLongText,
        popupButtonNoticeShortCta,
    } = attributes;

    return (
        <div {...blockProps}>
            <ServerSideRender block="give/donation-form-block" attributes={attributes} />

            <InspectorControls>
                <PanelBody title={__('Display Settings', 'give')} initialOpen={true}>
                    <SelectControl
                        label={__('Display Type', 'give')}
                        value={displayType}
                        options={[
                            {label: __('Iframe (Embedded Form)', 'give'), value: 'iframe'},
                            {label: __('Popup (Modal Button)', 'give'), value: 'popup'},
                        ]}
                        onChange={(value) => setAttributes({displayType: value})}
                    />
                    {displayType === 'popup' && (
                        <>
                            <TextControl
                                label={__('Button text (CTA)', 'give')}
                                help={__(
                                    'Override the default "Donate Now" label. Leave empty to use the default.',
                                    'give'
                                )}
                                value={popupButtonText ?? ''}
                                onChange={(value) => setAttributes({popupButtonText: value ?? ''})}
                            />
                            <ToggleControl
                                label={__('Show campaign stats notice', 'give')}
                                help={__(
                                    'Display an info line below the button explaining that crypto/stock donations are not included in campaign statistics.',
                                    'give'
                                )}
                                checked={!!popupButtonNoticeEnable}
                                onChange={(value) => {
                                    if (value) {
                                        setAttributes({
                                            popupButtonNoticeEnable: true,
                                            popupButtonNoticeShortText:
                                                (popupButtonNoticeShortText ?? '').trim() || DEFAULT_NOTICE_SHORT_TEXT,
                                            popupButtonNoticeShortCta:
                                                (popupButtonNoticeShortCta ?? '').trim() || DEFAULT_NOTICE_CTA,
                                            popupButtonNoticeLongText:
                                                (popupButtonNoticeLongText ?? '').trim() || DEFAULT_NOTICE_LONG_TEXT,
                                        });
                                    } else {
                                        setAttributes({popupButtonNoticeEnable: value});
                                    }
                                }}
                            />
                            {popupButtonNoticeEnable && (
                                <>
                                    <TextControl
                                        label={__('Notice short text', 'give')}
                                        help={__(
                                            'Brief text shown next to the info icon. Leave empty for default.',
                                            'give'
                                        )}
                                        value={popupButtonNoticeShortText ?? ''}
                                        onChange={(value) => setAttributes({popupButtonNoticeShortText: value ?? ''})}
                                    />
                                    <TextControl
                                        label={__('Notice CTA (e.g. "Learn more")', 'give')}
                                        help={__(
                                            'Link text that opens the detailed explanation modal. Leave empty for default.',
                                            'give'
                                        )}
                                        value={popupButtonNoticeShortCta ?? ''}
                                        onChange={(value) => setAttributes({popupButtonNoticeShortCta: value ?? ''})}
                                    />
                                    <TextareaControl
                                        label={__('Notice long text (modal)', 'give')}
                                        help={__(
                                            'Detailed explanation shown in the modal. Leave empty for default.',
                                            'give'
                                        )}
                                        value={popupButtonNoticeLongText ?? ''}
                                        onChange={(value) => setAttributes({popupButtonNoticeLongText: value ?? ''})}
                                        rows={4}
                                    />
                                </>
                            )}
                        </>
                    )}
                </PanelBody>
            </InspectorControls>
        </div>
    );
}
