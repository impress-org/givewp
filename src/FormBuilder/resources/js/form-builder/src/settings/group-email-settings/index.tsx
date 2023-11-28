import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import EmailTemplateOptions from './email/template-options';
import {useFormState} from '@givewp/form-builder/stores/form-state';

const getEmailSettings = () => {
    const {emailNotifications} = getFormBuilderWindowData();
    const {
        settings: {emailOptionsStatus},
    } = useFormState();
    let emailSettings = [];

    if (emailOptionsStatus === 'enabled') {
        emailSettings = emailNotifications.map((emailNotification) => {
            const {id, title} = emailNotification;

            return {
                name: title,
                path: `email-settings/${id}`,
                element: <EmailTemplateOptions notification={id} />,
            };
        });
    }

    return emailSettings;
}

export default getEmailSettings;
