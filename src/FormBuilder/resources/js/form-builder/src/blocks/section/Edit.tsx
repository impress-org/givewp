import {__} from '@wordpress/i18n';

import {InnerBlocks, InspectorControls, RichText} from '@wordpress/block-editor';

import {PanelBody, PanelRow, TextareaControl, TextControl} from '@wordpress/components';

import {useSelect} from '@wordpress/data';
import {BlockEditProps} from '@wordpress/blocks';

export default function Edit(props: BlockEditProps<any>) {
    const {
        attributes: {title, description},
        setAttributes,
    } = props;

    const isParentOfSelectedBlock = useSelect<any>(
        (select) => select('core/block-editor').hasSelectedInnerBlock(props.clientId, true),
        []
    );
    const isSelectedOrIsInnerBlockSelected = props.isSelected || isParentOfSelectedBlock;

    return (
        <>
            <div className="block-editor-block-list__layout-section">
                <header style={{display: 'flex', flexDirection: 'column', gap: '8px'}}>
                    {title.length > 0 && (
                        <RichText
                            tagName="h2"
                            value={title}
                            onChange={(val) => setAttributes({title: val})}
                            style={{margin: '0', fontSize: '22px', fontWeight: 700}}
                            allowedFormats={[]}
                        />
                    )}
                    {description.length > 0 && (
                        <RichText
                            tagName="p"
                            value={description}
                            onChange={(val) => setAttributes({description: val})}
                            style={{fontSize: '16px', fontWeight: 500}}
                            allowedFormats={[]}
                        />
                    )}
                </header>

                <InnerBlocks
                    allowedBlocks={
                        [] /* This prevents nested sections. Empty array is overwritten by child blocks specifying a parent. */
                    }
                    template={props.attributes.innerBlocksTemplate}
                    renderAppender={InnerBlocks.DefaultBlockAppender}
                />
            </div>

            <InspectorControls>
                <PanelBody title={__('Attributes', 'give')} initialOpen={true}>
                    <PanelRow>
                        <TextControl label={'Title'} value={title} onChange={(val) => setAttributes({title: val})} />
                    </PanelRow>
                    <PanelRow>
                        <TextareaControl
                            label={'Description'}
                            value={description}
                            onChange={(val) => setAttributes({description: val})}
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>
        </>
    );
}
