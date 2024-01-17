import { getFormBuilderWindowData } from "@givewp/form-builder/common/getWindowData";
import EmailTemplateOptions from "./email/template-options";

/**
 * @since 3.3.0
 */
const getEmailSettings = () => {
    const {emailNotifications} = getFormBuilderWindowData();

    return emailNotifications.map((emailNotification) => {
        const {id, title} = emailNotification;

        return {
            name: title,
            path: `email-settings/${id}`,
            element: (props) => <EmailTemplateOptions notification={id} {...props} />,
            showWhen: areEmailSettingsEnabled,
        };
    });
};

/**
 * @since 3.3.0
 */
const areEmailSettingsEnabled = ({settings}) => {
    const {emailOptionsStatus} = settings;

    return emailOptionsStatus === 'enabled';
};

export default getEmailSettings;
