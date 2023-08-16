import type {
    EmailNotification,
    FormDesign,
    FormPageSettings,
    Gateway,
    TemplateTag,
    TermsAndConditions,
} from '@givewp/form-builder/types';

import BlockRegistrar from '@givewp/form-builder/registrars/blocks';

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
    recurringAddonData?: {
        isInstalled: boolean;
    };
    gatewaySettingsUrl: string;
    emailPreviewURL: string;
    emailTemplateTags: TemplateTag[];
    emailNotifications: EmailNotification[];
    emailDefaultAddress: string;
    disallowedFieldNames: string[];
    donationConfirmationTemplateTags: TemplateTag[];
    termsAndConditions: TermsAndConditions;
}

/**
 * @since 3.0.0
 */
export default function getWindowData(): FormBuilderWindowData {
    // @ts-ignore
    return window.storageData;
}

/**
 * @since 3.0.0
 */
export function getFormBuilderWindowData(): FormBuilderWindowData {
    // @ts-ignore
    return window.storageData;
}

/**
 * @since 3.0.0
 */
export function getBlockRegistrar(): BlockRegistrar {
    // @ts-ignore
    return window.givewp.form.blocks;
}
