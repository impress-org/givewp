import { __ } from "@wordpress/i18n";
import { PanelRow, SelectControl, TextControl, ToggleControl } from "@wordpress/components";
import { SettingsSection } from "@givewp/form-builder-library";
import MediaLibrary from "../../../components/media-library";

/**
 * @since 3.3.0
 */
export default function EmailGeneralSettings({ settings, setSettings }) {
    const { emailOptionsStatus, emailTemplate, emailLogo, emailFromName, emailFromEmail } = settings;

    return (
        <>
            <SettingsSection
                title={__('Email notifications', 'give')}
                description={__(
                    'GiveWP sends emails to both donors and specified site admins for various purposes.',
                    'give'
                )}
            >
                <PanelRow className={'no-extra-gap'}>
                    <ToggleControl
                        label={__('Customize email options', 'give')}
                        help={__('Uses global settings when disabled.', 'give')}
                        checked={emailOptionsStatus === 'enabled'}
                        onChange={(emailOptionsStatus) => {
                            setSettings({ emailOptionsStatus: emailOptionsStatus ? 'enabled' : 'global' });
                        }}
                    />
                </PanelRow>
            </SettingsSection>
            {emailOptionsStatus === 'enabled' && (
                <SettingsSection
                    title={__('Template details', 'give')}
                    description={__('Set the content structure for the email', 'give')}
                >
                    <PanelRow>
                        <SelectControl
                            label={__('Email Template', 'give')}
                            help={__('Choose your template from the available registered template types', 'give')}
                            options={[
                                {label: __('Default template', 'give'), value: 'default'},
                                {label: __('No template, plain text only', 'give'), value: 'none'},
                            ]}
                            value={emailTemplate}
                            onChange={(emailTemplate) => setSettings({ emailTemplate })}
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            label={__('From Name', 'give')}
                            help={__(
                                'The name which appears in the "From" field in all GiveWP donation emails.',
                                'give'
                            )}
                            value={emailFromName}
                            onChange={(emailFromName) => setSettings({ emailFromName })}
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            label={__('From Email', 'give')}
                            help={__(
                                'Email address from which all GiveWP emails are sent from. This will act as the "from" and "reply-to" email address.',
                                'give'
                            )}
                            value={emailFromEmail}
                            onChange={(emailFromEmail) => setSettings({ emailFromEmail })}
                        />
                    </PanelRow>
                    <PanelRow>
                        <MediaLibrary
                            label={__('Logo URL', 'give')}
                            value={emailLogo}
                            onChange={(emailLogo) => setSettings({ emailLogo })}
                            help={__(
                                'Upload or choose a logo to be displayed at the top of the donation receipt emails. Displayed on HTML emails only.',
                                'give'
                            )}
                        />
                    </PanelRow>
                </SettingsSection>
            )}
        </>
    );
}
