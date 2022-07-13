import {PanelBody, PanelRow, TextControl} from "@wordpress/components";
import {__} from "@wordpress/i18n";
import {useFormSettings} from "../context";

const FormTitleSettings = () => {

    const [{formTitle}, updateFormSetting] = useFormSettings();

    return (
        <PanelBody>
            <PanelRow>
                <TextControl
                    label={__('Form Title')}
                    value={formTitle}
                    onChange={(formTitle) => updateFormSetting({formTitle})}
                />
            </PanelRow>
        </PanelBody>
    );
};

export default FormTitleSettings;
