import {useFormSettings} from "../context";
import {__} from "@wordpress/i18n";
import {PanelBody, PanelRow, SelectControl, ToggleControl} from "@wordpress/components";

const FormFieldSettings = () => {

    const [{registration, anonymousDonations, guestDonations}, updateSetting] = useFormSettings()


    const registrationOptions = [
        {
            value: 'none',
            label: __('None', 'give'),
        },
        {
            value: 'registration',
            label: __('Registration', 'give'),
        },
        {
            value: 'login',
            label: __('Login', 'give'),
        },
        {
            value: 'register_and_login',
            label: __('Registration + Login', 'give'),
        },
    ]

    return (
        <PanelBody title={__('Form Fields', 'give')} initialOpen={false}>
            <PanelRow>
                <SelectControl
                    labelPosition={'left'}
                    label={__('Registration', 'give')}
                    help={__('Display the registration and/or login forms in the payment section for non-logged-in users.', 'give')}
                    value={registration}
                    options={registrationOptions}
                    onChange={(registration) => updateSetting({registration: registration})}
                />
            </PanelRow>
            <PanelRow>
                <ToggleControl
                    label={__('Anonymous Donations', 'give')}
                    checked={anonymousDonations}
                    onChange={() => updateSetting({anonymousDonations: !anonymousDonations})}
                />
            </PanelRow>
            <PanelRow>
                <ToggleControl
                    label={__('Allow Guest Donations', 'give')}
                    checked={guestDonations}
                    onChange={() => updateSetting({guestDonations: !guestDonations})}
                />
            </PanelRow>
        </PanelBody>
    )
}

export default FormFieldSettings
