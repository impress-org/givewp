import {BlockEditProps} from '@wordpress/blocks';
import {RichText, InspectorControls} from '@wordpress/block-editor';
import {__} from '@wordpress/i18n';
import {PanelBody, PanelRow, TextareaControl, TextControl} from "@wordpress/components";

export default function Edit({attributes, setAttributes}: BlockEditProps<any>) {
    const {content} = attributes;

    return (
        <>
            <RichText
                tagName="p"
                value={content}
                allowedFormats={['core/bold', 'core/italic', 'core/link']}
                onChange={(content) => setAttributes({content})}
                placeholder={__('Enter some text', 'custom-block-editor')}
            />
            <InspectorControls>
                <PanelBody title={__('Attributes', 'give')} initialOpen={true}>
                    <PanelRow>
                        <TextareaControl
                            label={__('Content', 'give')}
                            value={content}
                            onChange={(content) => setAttributes({content})}
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>
        </>
    );
}
