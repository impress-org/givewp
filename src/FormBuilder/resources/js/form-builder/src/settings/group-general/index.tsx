import {__} from '@wordpress/i18n';
import {settings} from '@wordpress/icons';

import SettingsGroup from '@givewp/form-builder/components/canvas/FormSettingsContainer/components/SettingsGroup';
import BlockCard from '@givewp/form-builder/components/forks/BlockCard';
import {FormGridSettings, FormSummarySettings, RegistrationSettings} from '@givewp/form-builder/settings';
import SettingsSection from '@givewp/form-builder/components/canvas/FormSettingsContainer/components/SettingsSection';

export default function FormGeneralSettingsGroup() {
    return (
        <SettingsGroup item="item-general" title={__('General', 'give')}>
            <SettingsSection
                title={__('General', 'give')}
                description={__('This includes the form name, the permalink and the visibility of this form.', 'give')}
            >
                <BlockCard
                    icon={settings}
                    title="Form Settings"
                    description={__(
                        'These settings affect how your form functions and is presented, as well as the form page.',
                        'give'
                    )}
                />
                <FormSummarySettings />
            </SettingsSection>
            <SettingsSection
                title={__('User Registration', 'give')}
                description={__(
                    'Notify donors that they have an account they can use to manage their donations',
                    'give'
                )}
            >
                <RegistrationSettings />
            </SettingsSection>
            <SettingsSection title={__('Form Grid', 'give')}>
                <FormGridSettings />
            </SettingsSection>
        </SettingsGroup>
    );
}
