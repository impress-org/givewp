import {PanelBody, PanelRow, SelectControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormSettings, useFormSettingsDispatch} from '../../stores/form-settings';
import {FormTemplate} from "@givewp/form-builder/types";

declare global {
    interface Window {
        storageData?: {
            templates: FormTemplate[],
        }
    }
}

const TemplateSettings = () => {
    const {template} = useFormSettings();
    const dispatch = useFormSettingsDispatch();

    const templateOptions = Object.values(window?.storageData?.templates).map(({id, name}) => {
        return { value: id, label: name}
    })

    return (
        <PanelBody>
            <PanelRow>
                <SelectControl
                    label={__('Form template', 'givewp')}
                    value={template}
                    onChange={(template) => dispatch(setFormSettings({template}))}
                    options={templateOptions}
                />
            </PanelRow>
        </PanelBody>
    );
};

export default TemplateSettings;
