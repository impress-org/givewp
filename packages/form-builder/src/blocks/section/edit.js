import {__} from "@wordpress/i18n";

import {
    RichText,
    InspectorControls,
    InnerBlocks,
} from "@wordpress/block-editor";

import {
    PanelBody,
    PanelRow,
    TextControl,
    TextareaControl,
} from "@wordpress/components";

import {useSelect} from '@wordpress/data';

export default function Edit(props) {

    const {
        attributes: {title, description},
        setAttributes,
    } = props;

    const isParentOfSelectedBlock = useSelect((select) => select('core/block-editor').hasSelectedInnerBlock(props.clientId, true));
    const isSelectedOrIsInnerBlockSelected = props.isSelected || isParentOfSelectedBlock;
    const borderColor = isSelectedOrIsInnerBlockSelected ? '#66bb6a' : 'lightgray';

    return (
        <>
            <div style={{
                display: 'flex',
                flexDirection: 'column',
                gap: '20px',
                marginBottom: '20px',
                outline: '1px solid ' + borderColor,
                borderRadius: '5px',
                padding: '0 40px',
            }}>
                <header>
                    <RichText
                        tagName="h2"
                        value={title}
                        onChange={(val) => setAttributes({title: val})}
                        style={{borderBottom: '0.0625rem solid #ddd'}}
                    />
                    <RichText
                        tagName="p"
                        value={description}
                        onChange={(val) => setAttributes({description: val})}
                    />
                </header>

                <InnerBlocks
                    allowedBlocks={[] /* This prevents nested sections. Empty array is overwritten by child blocks specifying a parent. */}
                    template={props.attributes.innerBlocksTemplate}
                    renderAppender={!!isSelectedOrIsInnerBlockSelected && InnerBlocks.ButtonBlockAppender}
                />

            </div>

            <InspectorControls>
                <PanelBody title={__('Attributes', 'give')} initialOpen={true}>
                    <PanelRow>
                        <TextControl
                            label={'Title'}
                            value={title}
                            onChange={(val) => setAttributes({title: val})}
                        />
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
