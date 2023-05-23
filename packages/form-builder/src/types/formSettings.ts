import {FormStatus} from "@givewp/form-builder/types/formStatus";

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
    registration: string;
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
};
