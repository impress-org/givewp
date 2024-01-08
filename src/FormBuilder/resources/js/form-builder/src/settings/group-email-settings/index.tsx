import { getFormBuilderWindowData } from "@givewp/form-builder/common/getWindowData";
import EmailTemplateOptions from "./email/template-options";

/**
 * @unreleased
 */
const getEmailSettings = () => {
    const {emailNotifications} = getFormBuilderWindowData();

    return emailNotifications.map((emailNotification) => {
        const {id, title} = emailNotification;

        return {
            name: title,
            path: `email-settings/${id}`,
            element: <EmailTemplateOptions notification={id} />,
            showWhen: areEmailSettingsEnabled,
        };
    });
};

/**
 * @unreleased
 */
const areEmailSettingsEnabled = ({settings}) => {
    const {emailOptionsStatus} = settings;

    return emailOptionsStatus === 'enabled';
};

export default getEmailSettings;
