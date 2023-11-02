import {InspectorAdvancedControls, InspectorControls} from '@wordpress/block-editor';
import {useCallback, useMemo} from '@wordpress/element';
import {getBlockSupport} from '@wordpress/blocks';
import normalizeFieldSettings from '@givewp/form-builder/supports/field-settings/normalizeFieldSettings';
import {useFieldNameValidator} from '@givewp/form-builder/hooks';
import {PanelBody, PanelRow, TextareaControl, TextControl, ToggleControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import Label from '@givewp/form-builder/blocks/fields/settings/Label';

import {FieldSettings} from './types';
import {AfterDisplaySettingsSlot, AfterFieldSettingsSlot, DisplaySettingsSlot, FieldSettingsSlot} from './slots';
import {createHigherOrderComponent} from '@wordpress/compose';
import {GiveWPSupports} from '@givewp/form-builder/supports/types';
import {useEffect, useState} from 'react';
import MetaKeyTextControl, {slugifyMeta} from '@givewp/form-builder/supports/field-settings/MetaKeyTextControl';

/**
 * Higher Order Component that adds field settings to the inspector controls.
 *
 * @since 3.0.0
 */
const FieldSettingsHOC = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        const {name, attributes, setAttributes, clientId} = props;

        const fieldSettings: FieldSettings = useMemo(() => {
            // @ts-ignore
          const giveSupports = getBlockSupport(name, 'givewp') as GiveWPSupports;

            return normalizeFieldSettings(giveSupports?.fieldSettings);
        }, [name]);

        if (!fieldSettings) {
            return <BlockEdit {...props} />;
        }

        return (
            <>
                <BlockEdit {...props} />
                <FieldSettingsEdit
                    attributes={attributes}
                    setAttributes={setAttributes}
                    fieldSettings={fieldSettings}
                    clientId={clientId}
                />
            </>
        );
    };
}, 'withInspectorControl');

export default FieldSettingsHOC;

const generateEmailTag = (fieldName, storeAsDonorMeta) => {
    return storeAsDonorMeta ? `meta_donor_${fieldName}` : `meta_donation_${fieldName}`;
};

/**
 * Renders the field settings inspector controls.
 *
 * @since 3.0.0
 */
function FieldSettingsEdit({attributes, setAttributes, fieldSettings, clientId}) {
    const validateFieldName = useFieldNameValidator();
    const [hasFieldNameAttribute, setHasFieldNameAttribute] = useState<boolean>(attributes.hasOwnProperty('fieldName'));
    const [isNewField] = useState<boolean>(attributes.metaUUID !== clientId);

    const updateFieldName = useCallback(
        (newFieldName = null, bumpUniqueness = false) => {
            let slugifiedName = newFieldName ? slugifyMeta(newFieldName) : null;

            if (!slugifiedName) {
                slugifiedName = slugifyMeta(attributes.label);
            }

            const [isUnique, suggestedName] = validateFieldName(slugifiedName, bumpUniqueness);

            if (!isUnique) {
                slugifiedName = suggestedName;
            }

            setAttributes({
                fieldName: slugifiedName,
                emailTag: generateEmailTag(slugifiedName, attributes.storeAsDonorMeta),
            });
        },
        [setAttributes, attributes.label]
    );

    const handleLabelBlur = useCallback(
        (event) => {
            if (!hasFieldNameAttribute) {
                updateFieldName(event.target.value);
                setHasFieldNameAttribute(true);
            }
        },
        [hasFieldNameAttribute, updateFieldName]
    );

    const enforceFieldName = useCallback(() => {
        if (!attributes.fieldName) {
            updateFieldName();
        } else {
            updateFieldName(attributes.fieldName, true);
        }
    }, [attributes.fieldName, updateFieldName]);

    useEffect(() => {
        // The first time the field is rendered set the field name to make sure the default meta key doesn't conflict
        // with any existing meta keys.
        if (isNewField) {
            updateFieldName();
            setHasFieldNameAttribute(false);
            setAttributes({metaUUID: clientId});
        }
    }, []);

    if (!attributes.hasOwnProperty('fieldName')) {
        updateFieldName();
    }

    return (
        <>
            <InspectorControls>
                {(fieldSettings.label ||
                    fieldSettings.placeholder ||
                    fieldSettings.description ||
                    fieldSettings.required) && (
                    <PanelBody title={__('Field Settings', 'give')} initialOpen={true}>
                        {fieldSettings.label && (
                            <PanelRow>
                                <Label
                                    label={attributes.label}
                                    setAttributes={setAttributes}
                                    onBlur={handleLabelBlur}
                                />
                            </PanelRow>
                        )}
                        {fieldSettings.placeholder && (
                            <PanelRow>
                                <TextControl
                                    label={__('Placeholder', 'give')}
                                    value={attributes.placeholder}
                                    onChange={(newPlaceholder) => {
                                        setAttributes({placeholder: newPlaceholder});
                                    }}
                                />
                            </PanelRow>
                        )}
                        {fieldSettings.description && (
                            <PanelRow>
                                <TextareaControl
                                    label={__('Description', 'give')}
                                    value={attributes.description}
                                    onChange={(newDescription) => {
                                        setAttributes({description: newDescription});
                                    }}
                                />
                            </PanelRow>
                        )}
                        {fieldSettings.required && (
                            <PanelRow>
                                <ToggleControl
                                    label={__('Required', 'give')}
                                    checked={attributes.isRequired}
                                    onChange={() => setAttributes({isRequired: !attributes.isRequired})}
                                />
                            </PanelRow>
                        )}
                        {/* @ts-ignore */}
                        <FieldSettingsSlot />
                    </PanelBody>
                )}

                <AfterFieldSettingsSlot />

                {(fieldSettings.displayInAdmin || fieldSettings.displayInReceipt) && (
                    <PanelBody title={__('Display Settings', 'give')} initialOpen={true}>
                        {fieldSettings.displayInAdmin && (
                            <PanelRow>
                                <ToggleControl
                                    label={__('Display in Admin', 'give')}
                                    checked={attributes.displayInAdmin}
                                    onChange={() => setAttributes({displayInAdmin: !attributes.displayInAdmin})}
                                />
                            </PanelRow>
                        )}
                        {fieldSettings.displayInReceipt && (
                            <PanelRow>
                                <ToggleControl
                                    label={__('Display in Receipt', 'give')}
                                    checked={attributes.displayInReceipt}
                                    onChange={() => setAttributes({displayInReceipt: !attributes.displayInReceipt})}
                                />
                            </PanelRow>
                        )}
                        {/* @ts-ignore */}
                        <DisplaySettingsSlot />
                    </PanelBody>
                )}
                <AfterDisplaySettingsSlot />
            </InspectorControls>
            {(fieldSettings.defaultValue ||
                fieldSettings.metaKey ||
                fieldSettings.storeAsDonorMeta ||
                fieldSettings.emailTag) && (
                <InspectorAdvancedControls>
                    {fieldSettings.defaultValue && (
                        <PanelRow>
                            <TextControl
                                label={__('Default Value', 'give')}
                                value={attributes.defaultValue}
                                help={[
                                    __(
                                        'The value of the field if the donor does not fill out this field. Leave blank in most cases.',
                                        'give'
                                    ),
                                ]}
                                onChange={(newDefaultValue) => setAttributes({defaultValue: newDefaultValue})}
                            />
                        </PanelRow>
                    )}

                    {fieldSettings.metaKey && (
                        <PanelRow>
                            <MetaKeyTextControl
                                value={attributes.fieldName}
                                onChange={(newName) => setAttributes({fieldName: newName})}
                                onBlur={enforceFieldName}
                                lockValue={!isNewField}
                            />
                        </PanelRow>
                    )}

                    {fieldSettings.metaKey && fieldSettings.emailTag && (
                        <PanelRow>
                            <TextControl
                                label={__('Email Tag', 'give')}
                                value={'{' + attributes.emailTag + '}'}
                                readOnly
                                help={[
                                    __(
                                        'Use this email tag to dynamically output the data in supported GiveWP emails.',
                                        'give'
                                    ),
                                ]}
                                onChange={() => {}}
                            />
                        </PanelRow>
                    )}

                    {fieldSettings.storeAsDonorMeta && (
                        <PanelRow>
                            <ToggleControl
                                label={__('Save to Donor Record', 'give')}
                                checked={attributes.storeAsDonorMeta}
                                onChange={() => {
                                    const storeAsDonorMeta = !attributes.storeAsDonorMeta;
                                    setAttributes({
                                        storeAsDonorMeta,
                                        emailTag: generateEmailTag(attributes.fieldName, storeAsDonorMeta),
                                    });
                                }}
                                help={__(
                                    "If enabled, the data collected by this field is saved to the Donor record instead of the Donation record. This is useful for data that doesn't normally change between donations, like a phone number or t-shirt size.",
                                    'give'
                                )}
                            />
                        </PanelRow>
                    )}
                </InspectorAdvancedControls>
            )}
        </>
    );
}
