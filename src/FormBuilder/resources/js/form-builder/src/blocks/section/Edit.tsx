import {__} from '@wordpress/i18n';

import {InnerBlocks, InspectorControls, RichText, store as blockEditorStore} from '@wordpress/block-editor';
import {PanelBody, PanelRow, TextareaControl, TextControl} from '@wordpress/components';
import {useSelect} from '@wordpress/data';
import {BlockEditProps} from '@wordpress/blocks';
import {getBlockRegistrar} from "@givewp/form-builder/common/getWindowData";

import BaseEmptyBlockInserter from './EmptyBlockInserter';
import './styles.scss';

export default function Edit(props: BlockEditProps<any>) {
    const {
        attributes: {title, description},
        setAttributes,
        clientId,
    } = props;

    const hasChildBlocks = useSelect(
        (select) => {
            // @ts-ignore
            const {getBlockOrder} = select(blockEditorStore);

            return getBlockOrder(clientId).length > 0;
        },
        [clientId]
    );

    const EmptyBlockInserter = () => {
        return (
            <div className="give-section__empty-block-inserter">
                {/* @ts-ignore */}
                <BaseEmptyBlockInserter rootClientId={clientId} />
            </div>
        );
    };

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
                    allowedBlocks={getBlockRegistrar()
                        .getAll()
                        .filter((block) => block.name !== 'givewp/section')
                        .map((block) => block.name)}
                    template={props.attributes.innerBlocksTemplate}
                    renderAppender={hasChildBlocks ? InnerBlocks.DefaultBlockAppender : EmptyBlockInserter}
                    prioritizedInserterBlocks={[
                        'givewp/text',
                        'givewp/paragraph',
                        'givewp-form-field-manager/dropdown',
                        'givewp-form-field-manager/phone',
                        'givewp-form-field-manager/radio',
                        'givewp-form-field-manager/html',
                    ]}
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
