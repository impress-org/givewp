import {InspectorControls} from "@wordpress/block-editor";
import {PanelBody, PanelRow, TextControl} from "@wordpress/components";
import { __ } from "@wordpress/i18n"

const settings = {

    title: __( 'Field', 'custom-block-editor' ),

    supports: {
        html: false, // Removes support for an HTML mode.
    },

    attributes: {
        label: {
            type: 'string',
            source: 'attribute',
            default: __('Text Field', 'give'),
        },
        options: {
            type: 'array',
        }
    },

    edit: function( props ) {

        const {
            attributes: { label, options },
            setAttributes,
        } = props;

        return (
            <>
                <div>
                    {'undefined' === typeof options
                        ? <input style={{width: '100%'}} type="text" placeholder={label} />
                        : <select>{options.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}</select>
                    }
                </div>

                <InspectorControls>
                    <PanelBody title={ __( 'Field Settings', 'give' ) } initialOpen={true}>
                        <PanelRow>
                            <TextControl
                                label={'Label'}
                                value={ label }
                                onChange={ ( val ) => setAttributes( { label: val } ) }
                            />
                        </PanelRow>
                    </PanelBody>
                </InspectorControls>
            </>
        )
    },

    save: function() {
        return null; // Save as attributes - not rendered HTML.
    }
}

export default settings
