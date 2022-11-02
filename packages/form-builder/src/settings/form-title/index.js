import {PanelBody, PanelRow, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormSettings, useFormSettingsDispatch} from '../../stores/form-settings/index.tsx';

const FormTitleSettings = () => {
    const {formTitle} = useFormSettings();
    const dispatch = useFormSettingsDispatch();

    return (
        <PanelBody>
            <PanelRow>
                <TextControl
                    label={__('Form Title')}
                    value={formTitle}
                    onChange={(formTitle) => dispatch(setFormSettings({formTitle}))}
                />
            </PanelRow>
        </PanelBody>
    );
};

export default FormTitleSettings;
