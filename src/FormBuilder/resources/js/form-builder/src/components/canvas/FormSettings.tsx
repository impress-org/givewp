import React from 'react';
import FormSettingsContainer from '@givewp/form-builder/components/canvas/FormSettingsContainer';
import FormDonationConfirmationSettingsGroup from '@givewp/form-builder/settings/group-donation-confirmation';
import FormEmailSettingsGroup from '@givewp/form-builder/settings/group-email-settings';
import FormGeneralSettingsGroup from '@givewp/form-builder/settings/group-general';

export default function FormSettings() {
    return (
        <FormSettingsContainer>
            <FormGeneralSettingsGroup />
            <FormDonationConfirmationSettingsGroup />
            <FormEmailSettingsGroup />
            {wp.hooks.applyFilters('givewp_form_builder_settings', '')}
        </FormSettingsContainer>
    );
}
