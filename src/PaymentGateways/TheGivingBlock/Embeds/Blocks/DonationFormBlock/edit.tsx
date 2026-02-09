import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {BlockEditProps} from '@wordpress/blocks';
import {PanelBody, SelectControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

type BlockAttributes = {
    displayType: string;
};

/**
 * Block edit component for The Giving Block donation form.
 *
 * @unreleased
 */
export default function Edit({attributes, setAttributes}: BlockEditProps<BlockAttributes>) {
    const blockProps = useBlockProps();
    const {displayType} = attributes;

    return (
        <div {...blockProps}>
            <ServerSideRender block="give/donation-form-block" attributes={attributes} />

            <InspectorControls>
                <PanelBody title={__('Display Settings', 'give')} initialOpen={true}>
                    <SelectControl
                        label={__('Display Type', 'give')}
                        value={displayType ?? 'iframe'}
                        options={[
                            {label: __('Iframe (Embedded Form)', 'give'), value: 'iframe'},
                            {label: __('Popup (Modal Button)', 'give'), value: 'popup'},
                        ]}
                        onChange={(value) => setAttributes({displayType: value ?? 'iframe'})}
                    />
                </PanelBody>
            </InspectorControls>
        </div>
    );
}
