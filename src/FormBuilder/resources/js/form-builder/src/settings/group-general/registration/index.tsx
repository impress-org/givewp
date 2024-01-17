import { __ } from "@wordpress/i18n";
import { PanelRow, ToggleControl } from "@wordpress/components";

/**
 * @since 3.3.0
 */
const RegistrationSettings = ({settings, setSettings}) => {
    const {registrationNotification} = settings;

    return (
        <PanelRow className={'no-extra-gap'}>
            <ToggleControl
                label={__('Send new account notifications', 'give')}
                checked={registrationNotification}
                onChange={() => setSettings({registrationNotification: !registrationNotification})}
                help={__(
                    'When enabled, donors will be notified that they have an account they can use to manage their donations. Disable if you do not want donors to be aware of their account.',
                    'give'
                )}
            />
        </PanelRow>
    );
};

export default RegistrationSettings;
