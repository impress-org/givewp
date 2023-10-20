import {__} from '@wordpress/i18n';
import {settings} from '@wordpress/icons';
import BlockCard from '@givewp/form-builder/components/forks/BlockCard';
import {
    DonationConfirmation,
    EmailSettings,
    FormGridSettings,
    FormSummarySettings,
    RegistrationSettings,
} from '@givewp/form-builder/settings';

export default function FormSettings() {
    return (
        <>
            <BlockCard
                icon={settings}
                title="Form Settings"
                description={__(
                    'These settings affect how your form functions and is presented, as well as the form page.',
                    'give'
                )}
            />
            <FormSummarySettings />
            <RegistrationSettings />
            <DonationConfirmation />
            <FormGridSettings />
            <EmailSettings />
            {wp.hooks.applyFilters('givewp_form_builder_pdf_settings', '')}
        </>
    );
}
