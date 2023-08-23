import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import {__} from '@wordpress/i18n';
import {PanelBody, PanelRow, ToggleControl} from '@wordpress/components';

const RegistrationSettings = () => {
    const {
        settings: {registrationNotification},
    } = useFormState();
    const dispatch = useFormStateDispatch();

    return (
        <PanelBody title={__('User Registration', 'give')} initialOpen={false}>
            <PanelRow>
                <ToggleControl
                    label={__('Send new account notifications', 'give')}
                    checked={registrationNotification}
                    onChange={() => dispatch(setFormSettings({registrationNotification: !registrationNotification}))}
                />
            </PanelRow>
        </PanelBody>
    );
};

export default RegistrationSettings;
