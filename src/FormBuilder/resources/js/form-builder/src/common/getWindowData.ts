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
import {IntlTelInputSettings} from '@givewp/forms/propTypes';
import {FormColors} from '@givewp/forms/types';

type GoalTypeOption = {
    value: string;
    label: string;
    description: string;
    isCurrency: boolean;
};

/**
 * @since 4.1.0
 */
type GoalSourceOption = {
    value: string;
    label: string;
    description: string;
};

/**
 * @since 3.12.0
 */
type GoalProgressOption = {
    value: string;
    label: string;
    description: string;
    isCustom: boolean;
};

/**
 * @since 3.12.0 Added goalProgressOptions
 * @since 3.9.0 Added intlTelInputSettings
 * @since 3.7.0 Added isExcerptEnabled
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
        isLicensed: boolean;
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
    goalSourceOptions: GoalSourceOption[];
    goalProgressOptions: GoalProgressOption[];
    nameTitlePrefixes: string[];
    isExcerptEnabled: boolean;
    intlTelInputSettings: IntlTelInputSettings;
    campaignColors: FormColors;
    showFormGoalNotice: boolean;
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
