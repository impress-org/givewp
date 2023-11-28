import React from 'react';
import FormSettingsContainer from '@givewp/form-builder/components/canvas/FormSettingsContainer';
import FormDonationConfirmationSettingsGroup from '@givewp/form-builder/settings/group-donation-confirmation';
import FormGeneralSettingsGroup from '@givewp/form-builder/settings/group-general';
import {__} from '@wordpress/i18n';
import EmailGeneralSettings from '@givewp/form-builder/settings/group-email-settings/general';
import getEmailSettings from '@givewp/form-builder/settings/group-email-settings';

export default function FormSettings() {
    const additionalSettings = wp.hooks.applyFilters('givewp_form_builder_settings', []);

    const routes = [
        {
            name: __('General', 'give'),
            path: 'general',
            element: <FormGeneralSettingsGroup />,
        },
        {
            name: __('Donation Confirmation', 'give'),
            path: 'donation-confirmation',
            element: <FormDonationConfirmationSettingsGroup />,
        },
        {
            name: __('Email Settings', 'give'),
            path: 'email-settings',
            element: null,
            children: [
                {
                    name: __('General', 'give'),
                    path: 'email-settings/general',
                    element: <EmailGeneralSettings />,
                },
                ...getEmailSettings(),
            ],
        },
        ...additionalSettings,
    ];

    return <FormSettingsContainer routes={routes} />;
}
