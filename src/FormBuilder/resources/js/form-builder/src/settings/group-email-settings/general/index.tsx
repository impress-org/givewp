import {__} from '@wordpress/i18n';
import SettingsSection from '@givewp/form-builder/components/canvas/FormSettingsContainer/components/SettingsSection';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import {PanelRow, SelectControl, TextControl, ToggleControl} from '@wordpress/components';
import LogoUpload from '@givewp/form-builder/settings/group-email-settings/email/logo-upload';

export default function EmailGeneralSettings() {
    const {
        settings: {emailOptionsStatus, emailTemplate, emailLogo, emailFromName, emailFromEmail},
    } = useFormState();
    const dispatch = useFormStateDispatch();

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
                            dispatch(setFormSettings({emailOptionsStatus: emailOptionsStatus ? 'enabled' : 'global'}));
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
                            label={__('Email Template', 'givewp')}
                            help={__('Choose your template from the available registered template types', 'givewp')}
                            options={[
                                {label: __('Default template', 'givewp'), value: 'default'},
                                {label: __('No template, plain text only', 'givewp'), value: 'none'},
                            ]}
                            value={emailTemplate}
                            onChange={(emailTemplate) => dispatch(setFormSettings({emailTemplate}))}
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            label={__('From Name', 'givewp')}
                            help={__(
                                'The name which appears in the "From" field in all GiveWP donation emails.',
                                'givewp'
                            )}
                            value={emailFromName}
                            onChange={(emailFromName) => dispatch(setFormSettings({emailFromName}))}
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            label={__('From Email', 'givewp')}
                            help={__(
                                'Email address from which all GiveWP emails are sent from. This will act as the "from" and "reply-to" email address.',
                                'givewp'
                            )}
                            value={emailFromEmail}
                            onChange={(emailFromEmail) => dispatch(setFormSettings({emailFromEmail}))}
                        />
                    </PanelRow>
                    <PanelRow>
                        <LogoUpload
                            value={emailLogo}
                            onChange={(emailLogo) => dispatch(setFormSettings({emailLogo}))}
                        />
                    </PanelRow>
                </SettingsSection>
            )}
        </>
    );
}
