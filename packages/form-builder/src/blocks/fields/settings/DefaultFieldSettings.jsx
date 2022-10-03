import {PanelBody, PanelRow, TextControl, ToggleControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {InspectorControls} from '@wordpress/block-editor';
import {noop} from 'lodash';

export default function DefaultFieldSettings({attributes, setAttributes, onLabelTextControlBlur = noop}) {
    const {label, placeholder, isRequired, options} = attributes;
    const requiredClass = isRequired ? 'give-is-required' : '';

    return (
        <>
            <div>
                {'undefined' === typeof options ? (
                    <TextControl
                        label={label}
                        placeholder={placeholder}
                        required={isRequired}
                        className={requiredClass}
                    />
                ) : (
                    <select>
                        {options.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                )}
            </div>

            <InspectorControls>
                <PanelBody title={__('Field Settings', 'give')} initialOpen={true}>
                    <PanelRow>
                        <TextControl
                            label={__('Label', 'give')}
                            value={label}
                            onChange={(val) => setAttributes({label: val})}
                            onBlur={onLabelTextControlBlur}
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            label={__('Placeholder', 'give')}
                            value={placeholder}
                            onChange={(val) => setAttributes({placeholder: val})}
                        />
                    </PanelRow>
                    <PanelRow>
                        <ToggleControl
                            label={__('Required', 'give')}
                            checked={isRequired}
                            onChange={() => setAttributes({isRequired: !isRequired})}
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>
        </>
    );
}
