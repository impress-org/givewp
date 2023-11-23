import {__} from '@wordpress/i18n';
import SettingsGroup from '@givewp/form-builder/components/canvas/FormSettingsContainer/components/SettingsGroup';
import SettingsSection from '@givewp/form-builder/components/canvas/FormSettingsContainer/components/SettingsSection';
import {EmailSettings} from '@givewp/form-builder/settings';

export default function FormEmailSettingsGroup() {
    return (
        <SettingsGroup item="item-email-settings" title={__('Email Settings', 'give')}>
            <SettingsGroup item="item-email-settings-general" title={__('General', 'give')}>
                <SettingsSection title={__('Email Settings', 'give')}>
                    <EmailSettings />
                </SettingsSection>
            </SettingsGroup>
        </SettingsGroup>
    );
}
