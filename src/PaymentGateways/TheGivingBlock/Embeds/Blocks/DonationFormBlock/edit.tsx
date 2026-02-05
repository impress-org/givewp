import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { BlockEditProps } from '@wordpress/blocks';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Block edit component for The Giving Block donation form.
 * Popup button text is applied by the shared script (popupButtonText.js) in both editor and frontend.
 * We pass attributes directly to ServerSideRender so the HTML always has data-tgb-button-text.
 *
 * @unreleased
 */
export default function Edit({
    attributes,
    setAttributes,
}: BlockEditProps<{ displayType: string; popupButtonText: string }>) {
    const blockProps = useBlockProps();
    const { displayType, popupButtonText } = attributes;

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
                        <TextControl
                            label={__('Button text (CTA)', 'give')}
                            help={__(
                                'Override the default "Donate Now" label. Leave empty to use the default.',
                                'give'
                            )}
                            value={popupButtonText ?? ''}
                            onChange={(value) => setAttributes({ popupButtonText: value ?? '' })}
                        />
                    )}
                </PanelBody>
            </InspectorControls>
        </div>
    );
}
