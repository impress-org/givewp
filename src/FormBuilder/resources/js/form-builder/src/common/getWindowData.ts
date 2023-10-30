import {Component} from '@wordpress/element';
import type {
    EmailNotification,
    FormDesign,
    FormPageSettings,
    Gateway,
    TemplateTag,
    TermsAndConditions,
} from '@givewp/form-builder/types';

import BlockRegistrar from '@givewp/form-builder/registrars/blocks';

type GoalTypeOption = {
    value: string;
    label: string;
    description: string;
};

/**
 * @since 3.0.0
 */
interface FormBuilderWindowData {
    formId: number;
    nonce: string;
    resourceURL: string;
    previewURL: string;
    blockData: string;
    settings: string;
    formDesigns: FormDesign[];
    formPage: FormPageSettings;
    currency: string;
    gateways: Gateway[];
    formFieldManagerData?: {
        isInstalled: boolean;
    };
    recurringAddonData?: {
        isInstalled: boolean;
    };
    isRecurringEnabled: boolean;
    gatewaySettingsUrl: string;
    emailPreviewURL: string;
    emailTemplateTags: TemplateTag[];
    emailNotifications: EmailNotification[];
    emailDefaultAddress: string;
    disallowedFieldNames: string[];
    donationConfirmationTemplateTags: TemplateTag[];
    termsAndConditions: TermsAndConditions;
    goalTypeOptions: GoalTypeOption[];
}

/**
 * @unreleased
 */
interface FormBuilderSiteData {
    siteName: string;
    siteUrl: string;
}

/**
 * @since 3.0.0
 */
declare const window: {
    giveStorageData: FormBuilderWindowData;
    givewp: {
        form: {
            blocks: BlockRegistrar;
            components: {
                [key: string]: Component;
            };
        };
    },
    siteData: FormBuilderSiteData;
} & Window;

/**
 * @since 3.0.0
 */
export default function getWindowData(): FormBuilderWindowData {
    return window.giveStorageData;
}

/**
 * @since 3.0.0
 */
export function getFormBuilderWindowData(): FormBuilderWindowData {
    return window.giveStorageData;
}

/**
 * @since 3.0.0
 */
export function getBlockRegistrar(): BlockRegistrar {
    return window.givewp.form.blocks;
}
