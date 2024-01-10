import { __ } from "@wordpress/i18n";
import { createInterpolateElement } from "@wordpress/element";
import { SettingsSection } from "@givewp/form-builder-library";
import FormGridSettings from "./form-grid";
import FormSummarySettings from "./form-summary";
import RegistrationSettings from "./registration";

/**
 * @since 3.3.0
 */
export default function FormGeneralSettingsGroup({settings, setSettings}) {
    const formGridDescription = createInterpolateElement(
        __(
            'The GiveWP Form Grid provides a way to add a grid layout of multiple forms into posts and pages using either a block or shortcode. <a>Learn more about the Form Grid</a>',
            'give'
        ),
        {
            a: <a href="https://docs.givewp.com/form-grid-addon" target="_blank" />,
        }
    );

    return (
        <>
            <SettingsSection
                title={__('General', 'give')}
                description={__('This includes the form name, the permalink and the visibility of this form.', 'give')}
            >
                <FormSummarySettings settings={settings} setSettings={setSettings} />
            </SettingsSection>
            <SettingsSection
                title={__('User Registration', 'give')}
                description={__(
                    'Notify donors that they have an account they can use to manage their donations',
                    'give'
                )}
            >
                <RegistrationSettings settings={settings} setSettings={setSettings} />
            </SettingsSection>
            <SettingsSection title={__('Form Grid', 'give')} description={formGridDescription}>
                <FormGridSettings settings={settings} setSettings={setSettings} />
            </SettingsSection>
        </>
    );
}
