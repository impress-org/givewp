import {FormStatus} from "@givewp/form-builder/types/formStatus";
import {EmailTemplateOption} from "@givewp/form-builder/types/emailTemplateOption";

/**
 * @since 0.1.0
 */
export type FormSettings = {
    showHeader: boolean;
    showHeading: boolean;
    showDescription: boolean;
    formTitle: string;
    enableDonationGoal: boolean;
    enableAutoClose: boolean;
    goalAchievedMessage: string;
    registrationNotification: boolean;
    goalType: string;
    designId: string;
    heading: string;
    description: string;
    primaryColor: string;
    secondaryColor: string;
    customCss: string;
    pageSlug: string;
    receiptHeading: string;
    receiptDescription: string;
    formStatus: FormStatus;
    emailTemplateOptions: EmailTemplateOption[];
    emailOptionsStatus: string;
    emailTemplate: string;
    emailLogo: string;
    emailFromName: string;
    emailFromEmail: string;
    formGridCustomize: boolean;
    formGridRedirectUrl: string;
    formGridDonateButtonText: string;
    formGridHideDocumentationLink: boolean;
    offlineDonationsCustomize: boolean;
};
