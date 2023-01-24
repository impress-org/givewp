import {__} from '@wordpress/i18n';

import {InnerBlocks, InspectorControls, RichText} from '@wordpress/block-editor';

import {PanelBody, PanelRow, TextareaControl, TextControl} from '@wordpress/components';

import {useSelect} from '@wordpress/data';

export default function Edit(props) {
    const {
        attributes: {title, description},
        setAttributes,
    } = props;

    const isParentOfSelectedBlock = useSelect((select) =>
        select('core/block-editor').hasSelectedInnerBlock(props.clientId, true)
    );
    const isSelectedOrIsInnerBlockSelected = props.isSelected || isParentOfSelectedBlock;
    const borderColor = isSelectedOrIsInnerBlockSelected ? '#66bb6a' : 'lightgray';

    return (
        <>
            <div
                style={{
                    display: 'flex',
                    flexDirection: 'column',
                    gap: '24px',
                    marginBottom: '36px',
                    outline: '1px solid ' + borderColor,
                    borderRadius: '5px',
                    padding: '36px 40px 46px 40px',
                    backgroundColor: 'white',
                }}
            >
                <header style={{display: 'flex', flexDirection: 'column', gap: '8px'}}>
                    <RichText
                        tagName="h2"
                        value={title}
                        onChange={(val) => setAttributes({title: val})}
                        style={{margin: '0', fontSize: '22px', fontWeight: 700}}
                    />
                    <RichText
                        tagName="p"
                        value={description}
                        onChange={(val) => setAttributes({description: val})}
                        style={{fontSize: '16px', fontWeight: 500}}
                    />
                </header>

                <InnerBlocks
                    allowedBlocks={
                        [] /* This prevents nested sections. Empty array is overwritten by child blocks specifying a parent. */
                    }
                    template={props.attributes.innerBlocksTemplate}
                    renderAppender={InnerBlocks.ButtonBlockAppender}
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
