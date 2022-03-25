import {__} from '@wordpress/i18n';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {PanelBody, PanelRow, SelectControl} from '@wordpress/components';
import {Fragment} from '@wordpress/element';
import useFormOptions from './hooks/useFormOptions';

/**
 * @since 1.0.0
 * @param attributes
 * @param setAttributes
 * @returns {JSX.Element}
 * @constructor
 */
export default function Edit({attributes, setAttributes}) {
    const {formId} = attributes;
    const {formOptions} = useFormOptions();

    return (
        <Fragment>
            {/*block controls*/}
            <InspectorControls>
                <PanelBody title={__('Form Settings', 'give')} initialOpen={true}>
                    <PanelRow>
                        <SelectControl
                            label="Select Form"
                            value={formId}
                            options={formOptions}
                            onChange={(newFormId) => {
                                setAttributes({formId: Number(newFormId)});
                            }}
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>

            {/*block preview*/}
            <div {...useBlockProps()}>
                <p>GiveWP Next Gen Donation Form</p>
            </div>
        </Fragment>
    );
}
