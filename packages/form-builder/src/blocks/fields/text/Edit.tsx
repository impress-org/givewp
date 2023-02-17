import {__} from '@wordpress/i18n';
import FieldSettings from '../settings/Edit';
import {useFieldNameValidator} from '../../../hooks';
import {InspectorAdvancedControls, InspectorControls} from '@wordpress/block-editor';
import {ExternalLink, PanelBody, PanelRow, TextControl, ToggleControl} from '@wordpress/components';
import {slugify} from '@givewp/form-builder/common';
import {useCallback} from '@wordpress/element';
import {BlockEditProps} from '@wordpress/blocks';

export default function Edit({attributes, setAttributes}: BlockEditProps<any>) {
    const {fieldName, label, storeAsDonorMeta, displayInAdmin, displayInReceipt} = attributes;
    const validateFieldName = useFieldNameValidator();

    const updateFieldName = useCallback(
        (newFieldName) => {
            setAttributes({
                fieldName: slugify(newFieldName),
            });
        },
        [setAttributes]
    );

    const enforceUniqueFieldName = useCallback(() => {
        const [isUnique, suggestedName] = validateFieldName(fieldName, '');
        if (!isUnique) {
            updateFieldName(suggestedName);
        }
    }, [fieldName, updateFieldName, validateFieldName]);

    const enforceRequiredValue = useCallback(() => {
        if (!fieldName) {
            updateFieldName(label);
        }
    }, [fieldName, updateFieldName, label]);

    return (
        <>
            <FieldSettings
                attributes={attributes}
                setAttributes={setAttributes}
                onLabelTextControlBlur={(event) => {
                    if (!fieldName) {
                        updateFieldName(event.target.value);
                        enforceUniqueFieldName();
                    }
                }}
            />
            <InspectorControls>
                <PanelBody title={__('Display Settings', 'give')} initialOpen={true}>
                    <PanelRow>
                        <ToggleControl
                            label={__('Display in Admin', 'give')}
                            checked={displayInAdmin}
                            onChange={() => setAttributes({displayInAdmin: !displayInAdmin})}
                        />
                    </PanelRow>
                    <PanelRow>
                        <ToggleControl
                            label={__('Display in Receipt', 'give')}
                            checked={displayInReceipt}
                            onChange={() => setAttributes({displayInReceipt: !displayInReceipt})}
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>
            <InspectorAdvancedControls>
                <PanelRow>
                    <ToggleControl
                        label={__('Store as Donor Meta', 'give')}
                        checked={storeAsDonorMeta}
                        onChange={() => setAttributes({storeAsDonorMeta: !storeAsDonorMeta})}
                        help={__('By default, fields are stored as Donation Meta', 'give')}
                    />
                </PanelRow>
                <PanelRow>
                    <TextControl
                        label={__('Field Name', 'give')}
                        value={fieldName}
                        help={[
                            <>{__('The programmatic name of the field used by the Fields API.', 'give')}</>,
                            <ExternalLink
                                style={{display: 'block', marginTop: '8px'}}
                                href="https://github.com/impress-org/givewp/tree/develop/src/Framework/FieldsAPI"
                            >
                                {__('Learn more about the Fields API', 'give')}
                            </ExternalLink>,
                        ]}
                        onChange={updateFieldName}
                        onBlur={() => {
                            enforceRequiredValue();
                            enforceUniqueFieldName();
                        }}
                    />
                </PanelRow>
            </InspectorAdvancedControls>
        </>
    );
}