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
    isCurrency: boolean;
};

/**
 * @unreleased Added isExcerptEnabled
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
    nameTitlePrefixes: string[];
    isExcerptEnabled: boolean;
    IntlTelInput: {
        i18n: any;
        cssUrl: string;
        scriptUrl: string;
        utilsScriptUrl: string;
    };
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
    };
} & Window;

/**
 * @since 3.0.0
 */
export default function getWindowData(): FormBuilderWindowData {
    //console.log('window.giveStorageData: ', window.giveStorageData);
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
