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
    formFieldManagerData?: {
        isInstalled: boolean;
    };
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
declare const window: {
    storageData: FormBuilderWindowData;
    givewp: {
        form: {
            blocks: BlockRegistrar;
        };
    };
} & Window;

/**
 * @since 3.0.0
 */
export default function getWindowData(): FormBuilderWindowData {
    return window.storageData;
}

/**
 * @since 3.0.0
 */
export function getFormBuilderWindowData(): FormBuilderWindowData {
    return window.storageData;
}

/**
 * @since 3.0.0
 */
export function getBlockRegistrar(): BlockRegistrar {
    return window.givewp.form.blocks;
}
