import {SettingsSection} from "@givewp/form-builder-library";
import {__} from "@wordpress/i18n";
import {PanelRow} from "@wordpress/components";
import FormTagSetting from "./form-tags";
import FormCategorySetting from "./form-categories";
import getWindowData, {isFormCategoriesEnabled, isFormTagsEnabled} from "./windowData";

/**
 * @since 3.16.0
 */
const TaxonomySettings = ({settings, setSettings}) => {

    return (
        <div id={'give-form-settings__form-taxonomies'}>

            {isFormTagsEnabled() && (
                <SettingsSection title={__('Form Tags', 'give')}>
                    <PanelRow className={'no-extra-gap'}>
                        <FormTagSetting settings={settings} setSettings={setSettings} />
                    </PanelRow>
                </SettingsSection>
            )}

            {isFormCategoriesEnabled() && (
                <SettingsSection title={__('Form Categories', 'give')}>
                    <PanelRow className={'no-extra-gap'}>
                        <FormCategorySetting settings={settings} setSettings={setSettings} />
                    </PanelRow>
                </SettingsSection>
            )}
        </div>
    )
}

/**
 * @since 3.16.0
 */
export default function withTaxonomySettingsRoute (routes) {

    const isFormTagsEnabled = getWindowData().formTagsEnabled;
    const isFormCategoriesEnabled = getWindowData().formCategoriesEnabled;

    /**
     * @since 3.16.0
     */
    const getDynamicLabel = () => {
        return isFormTagsEnabled && isFormCategoriesEnabled ? __('Tags and Categories', '')
            : isFormTagsEnabled ? __('Form Tags', '')
                : isFormCategoriesEnabled ? __('Form Categories', '') : '';
    }

    return [
        ...routes,
        {
            name: getDynamicLabel(),
            path: 'give-form-tags',
            element: TaxonomySettings,
        },
    ];
}
