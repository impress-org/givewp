import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import EmailTemplateOptions from './email/template-options';
import {useFormState} from '@givewp/form-builder/stores/form-state';

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

const areEmailSettingsEnabled = () => {
    const {
        settings: {emailOptionsStatus},
    } = useFormState();

    return emailOptionsStatus === 'enabled';
};

export default getEmailSettings;
