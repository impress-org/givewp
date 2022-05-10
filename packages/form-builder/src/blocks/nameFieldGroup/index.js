import { registerBlockType } from "@wordpress/blocks";
import { __ } from "@wordpress/i18n"
import { Icon } from '@wordpress/icons';

import {
    InspectorControls,
    useBlockProps,
    useInnerBlocksProps,
} from "@wordpress/block-editor";

import {
    PanelBody,
    PanelRow,
    ToggleControl,
} from "@wordpress/components";

registerBlockType( 'custom-block-editor/name-field-group', {

    title: __( 'Name Field', 'custom-block-editor' ),

    category: 'layout',

    icon: () => <Icon icon={
        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fillRule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clipRule="evenodd"/>
        </svg>
    } />,

    supports: {
        html: false, // Removes support for an HTML mode.
    },

    attributes: {
        showHonorific: {
            type: 'boolean',
            default: true,
        }
    },

    edit: Edit,

    save: function() {
        return null; // Save as attributes - not rendered HTML.
    }
} );

function Edit( props ) {

    const blockProps = useBlockProps();
    const { children, ...innerBlocksProps } = useInnerBlocksProps( blockProps, {
        orientation: 'horizontal',
        templateLock: 'all',
        template: [
            [ 'custom-block-editor/honorific-name-field' ],
            [ 'custom-block-editor/first-name-field' ],
            [ 'custom-block-editor/last-name-field' ],
        ],
    } );

    // @todo Convert to hooks inorder to control layout
    // @link https://make.wordpress.org/core/2021/12/28/take-more-control-over-inner-block-areas-as-a-block-developer/
    return (
        <>
            <section {...blockProps}>
                <div {...innerBlocksProps}>
                    <div className={`nameFieldInnerBlocks ${ !props.attributes.showHonorific ? 'hideHonorific' : '' }`}>
                        {children}
                    </div>
                </div>
            </section>
            <InspectorControls>
                <PanelBody title={ __( 'Settings', 'give' ) } initialOpen={true}>
                    <PanelRow>
                        <ToggleControl
                            label={__('Show Honorific', 'give')}
                            checked={props.attributes.showHonorific}
                            onChange={() => props.setAttributes({ showHonorific: ! props.attributes.showHonorific })}
                            help={'This is help text.'}
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>
        </>
    )
}
