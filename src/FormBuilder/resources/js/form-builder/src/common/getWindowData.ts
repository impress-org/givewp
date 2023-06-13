import {
    FormDesign,
    FormPageSettings,
    Gateway,
    TemplateTag,
    EmailNotification,
} from '@givewp/form-builder/types';

declare global {
    interface Window {

        wp?: any;
        storageData?: {
            formId: number;
            nonce: string;
            formDesigns: FormDesign[];
            formPage: FormPageSettings;
            currency: string;
            gateways: Gateway[];
            recurringAddonData?: {
                isInstalled: boolean;
            },
            gatewaySettingsUrl: string;
            emailPreviewURL: string;
            emailTemplateTags: TemplateTag[];
            emailNotifications: EmailNotification[];
            emailDefaultAddress: string;
        },
    }
}

export default function getWindowData() {
    return window.storageData;
}

export function getStorageData() {
    return window.storageData;
}

export function getFormBuilderData() {
    return window.storageData;
}
