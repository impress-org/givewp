import { PanelRow } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { SettingsSection } from "@givewp/form-builder-library";
import FormTagSetting from "./form-tags";
import getWindowData from "./windowData";
import './style.scss';

wp.hooks.addFilter('givewp_form_builder_settings_additional_routes', 'give-form-tags', (settings) => {

    const isFormTagsEnabled = getWindowData().formTagsEnabled;
    const isFormCategoriesEnabled = getWindowData().formCategoriesEnabled;

    const getDynamicLabel = () => {
        return isFormTagsEnabled && isFormCategoriesEnabled ? __('Tags and Categories', '')
            : isFormTagsEnabled ? __('Form Tags', '')
                : isFormCategoriesEnabled ? __('Form Categories', '') : '';
    }

    return [
        ...settings,
        {
            name: getDynamicLabel(),
            path: 'give-form-tags',
            element: ({settings, setSettings}) => {
                return (
                    <div id={'give-form-settings__form-taxonomies'}>

                        {isFormTagsEnabled && (
                            <SettingsSection title={__('Form Tags', 'give')}>
                                <PanelRow className={'no-extra-gap'}>
                                    <FormTagSetting settings={settings} setSettings={setSettings} />
                                </PanelRow>
                            </SettingsSection>
                        )}

                        {isFormCategoriesEnabled && (
                            <SettingsSection title={__('Form Categories', 'give')}>
                                <PanelRow className={'no-extra-gap'}>
                                    FORM CATEGORY SETTINGS HERE
                                </PanelRow>
                            </SettingsSection>
                        )}
                    </div>
                )
            },
        },
    ];
});
