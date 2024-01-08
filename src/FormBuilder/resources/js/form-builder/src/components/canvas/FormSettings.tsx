import React, { useMemo } from "react";
import FormSettingsContainer from "@givewp/form-builder/components/canvas/FormSettingsContainer";
import FormDonationConfirmationSettingsGroup from "@givewp/form-builder/settings/group-donation-confirmation";
import FormGeneralSettingsGroup from "@givewp/form-builder/settings/group-general";
import { __ } from "@wordpress/i18n";
import EmailGeneralSettings from "@givewp/form-builder/settings/group-email-settings/general";
import getEmailSettings from "@givewp/form-builder/settings/group-email-settings";

/**
 * @unreleased
 */
export default function FormSettings() {
    const routes: Route[] = useMemo(() => {
        const additionalSettings = wp.hooks.applyFilters('givewp_form_builder_settings_additional_routes', []);

        return [
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
                childRoutes: [
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
    }, []);

    return <FormSettingsContainer routes={routes} />;
}

type Route = {
    name: string;
    path: string;
    element?: React.ReactElement;
    childRoutes?: Route[];
    showWhen?: () => boolean;
};
