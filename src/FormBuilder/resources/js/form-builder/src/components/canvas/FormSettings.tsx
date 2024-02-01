import React from "react";
import FormSettingsContainer from "@givewp/form-builder/components/canvas/FormSettingsContainer";
import FormGeneralSettingsGroup from "@givewp/form-builder/settings/group-general";
import FormDonationConfirmationSettingsGroup from "@givewp/form-builder/settings/group-donation-confirmation";
import { __ } from "@wordpress/i18n";
import EmailGeneralSettings from "@givewp/form-builder/settings/group-email-settings/general";
import getEmailSettings from "@givewp/form-builder/settings/group-email-settings";

/**
 * @since 3.3.0
 */
const routes: Route[] = [
    {
        name: __('General', 'give'),
        path: 'general',
        element: FormGeneralSettingsGroup,
    },
    {
        name: __('Donation Confirmation', 'give'),
        path: 'donation-confirmation',
        element: FormDonationConfirmationSettingsGroup,
    },
    {
        name: __('Email Settings', 'give'),
        path: 'email-settings',
        element: null,
        childRoutes: [
            {
                name: __('General', 'give'),
                path: 'email-settings/general',
                element: EmailGeneralSettings,
            },
            ...getEmailSettings(),
        ],
    },
    ...wp.hooks.applyFilters('givewp_form_builder_settings_additional_routes', []),
];

/**
 * @since 3.3.0
 */
export default function FormSettings() {
    return <FormSettingsContainer routes={validateRoutes(routes)} />;
}

/**
 * @since 3.3.0
 */
function validateRoutes(routes: Route[]): Route[] {
    const paths = [];

    return routes.filter((route) => {
        let isValid = isValidRoute(route);

        if (paths.includes(route.path)) {
            console.error(`${__('Duplicate path found in FormSettings routes:', 'give')} ${route.path}`);
            isValid = false;
        } else {
            paths.push(route.path);
        }

        if (route.childRoutes) {
            if (Array.isArray(route.childRoutes)) {
                route.childRoutes = validateRoutes(route.childRoutes);
            } else {
                delete route.childRoutes;
                isValid = false;
            }
        }

        return isValid;
    });
}

/**
 * @since 3.3.0
 */
function isValidRoute(route: Route) {
    return (
        route.name &&
        typeof route.name === 'string' &&
        route.path &&
        typeof route.path === 'string' &&
        'element' in route
    );
}

/**
 * @since 3.3.0
 */
export type Route = {
    name: string;
    path: string;
    element?: React.FC<{settings: any; setSettings: any}>;
    childRoutes?: Route[];
    showWhen?: ({item, settings}) => boolean;
};
