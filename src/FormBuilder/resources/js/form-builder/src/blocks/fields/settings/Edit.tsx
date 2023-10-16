import {PanelBody, PanelRow, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {InspectorControls} from '@wordpress/block-editor';
import {noop} from 'lodash';
import {BlockEditProps} from '@wordpress/blocks';
import {FocusEventHandler, PropsWithChildren} from 'react';
import Label from '@givewp/form-builder/blocks/fields/settings/Label';
import Placeholder from '@givewp/form-builder/blocks/fields/settings/Placeholder';
import Required from '@givewp/form-builder/blocks/fields/settings/Required';

interface Props extends Partial<BlockEditProps<any>> {
    onLabelTextControlBlur?: FocusEventHandler<HTMLInputElement>;
    showLabel?: boolean;
    showPlaceholder?: boolean;
    showRequired?: boolean;
}

type SettingsInspectorControlPanelProps = {
    title: string;
};

const SettingsInspectorControlPanel = ({title, children}: PropsWithChildren<SettingsInspectorControlPanelProps>) => {
    return (
        <InspectorControls>
            <PanelBody title={title} initialOpen={true}>
                {children}
            </PanelBody>
        </InspectorControls>
    );
};

export default function FieldSettings({
    attributes,
    setAttributes,
    onLabelTextControlBlur = noop,
    showLabel = true,
    showPlaceholder = true,
    showRequired = true,
}: Props) {
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
                        readOnly
                        onChange={null}
                        value={placeholder}
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

            <SettingsInspectorControlPanel title={__('Field Settings', 'give')}>
                {showLabel && (
                    <PanelRow>
                        <Label label={label} setAttributes={setAttributes} onBlur={onLabelTextControlBlur} />
                    </PanelRow>
                )}

                {showPlaceholder && (
                    <PanelRow>
                        <Placeholder placeholder={placeholder} setAttributes={setAttributes} />
                    </PanelRow>
                )}

                {showRequired && (
                    <PanelRow>
                        <Required isRequired={isRequired} setAttributes={setAttributes} />
                    </PanelRow>
                )}
            </SettingsInspectorControlPanel>
        </>
    );
}
