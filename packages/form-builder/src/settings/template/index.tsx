import {PanelBody, PanelRow, SelectControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormSettings, useFormSettingsDispatch} from '../../stores/form-settings/index.tsx';

const TemplateSettings = () => {
    const {template} = useFormSettings();
    const dispatch = useFormSettingsDispatch();

    const templateOptions = [
        {value: '', label: __('No Template', 'givewp')},
        {value: 'classic', label: __('Classic', 'givewp')},
    ]

    return (
        <PanelBody>
            <PanelRow>
                <SelectControl
                    labelPosition={'left'}
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
