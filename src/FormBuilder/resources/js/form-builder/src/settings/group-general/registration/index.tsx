import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import {__} from '@wordpress/i18n';
import {ToggleControl} from '@wordpress/components';

const RegistrationSettings = () => {
    const {
        settings: {registrationNotification},
    } = useFormState();
    const dispatch = useFormStateDispatch();

    return (
        <ToggleControl
            label={__('Send new account notifications', 'give')}
            checked={registrationNotification}
            onChange={() => dispatch(setFormSettings({registrationNotification: !registrationNotification}))}
            help={__(
                'When enabled, donors will be notified that they have an account they can use to manage their donations. Disable if you do not want donors to be aware of their account.',
                'give'
            )}
        />
    );
};

export default RegistrationSettings;
