import {Icon} from '@wordpress/icons';
import {__} from '@wordpress/i18n';
import defaultSettings from './settings';
import DefaultFieldSettings from './settings/DefaultFieldSettings';
import {useFieldNameValidator} from '../../hooks';
import {InspectorAdvancedControls, InspectorControls} from '@wordpress/block-editor';
import {ExternalLink, PanelBody, PanelRow, TextControl, ToggleControl} from '@wordpress/components';
import slugify from '../../common/slugify';
import {useCallback} from '@wordpress/element';

function FieldSettings({attributes, setAttributes}) {
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
        const [isUnique, suggestedName] = validateFieldName(fieldName);
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
            <DefaultFieldSettings
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

const field = {
    name: 'custom-block-editor/field',
    category: 'custom',
    settings: {
        ...defaultSettings,
        attributes: {
            ...defaultSettings.attributes,
            storeAsDonorMeta: {
                type: 'boolean',
                source: 'attribute',
                default: false,
            },
            displayInAdmin: {
                type: 'boolean',
                source: 'attribute',
                default: true,
            },
        },
        title: __('Text', 'custom-block-editor'),
        icon: () => (
            <Icon
                icon={
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M10.8449 6.89062L7.05176 16.5H9.1684L9.89932 14.6484H14.1006L14.8316 16.5H16.9482L13.155 6.89062H10.8449ZM10.6765 12.6797L12 9.32658L13.3235 12.6797H10.6765Z"
                            fill="#000C00"
                        />
                        <path
                            d="M18 2.625H6V0.75H0.75V6H2.625V18H0.75V23.25H6V21.375H18V23.25H23.25V18H21.375V6H23.25V0.75H18V2.625ZM2.25 4.5V2.25H4.5V4.5H2.25ZM4.5 21.75H2.25V19.5H4.5V21.75ZM18 19.875H6V18H4.125V6H6V4.125H18V6H19.875V18H18V19.875ZM21.75 19.5V21.75H19.5V19.5H21.75ZM19.5 2.25H21.75V4.5H19.5V2.25Z"
                            fill="#000C00"
                        />
                    </svg>
                }
            />
        ),
        edit: FieldSettings,
    },
};

export default field;
