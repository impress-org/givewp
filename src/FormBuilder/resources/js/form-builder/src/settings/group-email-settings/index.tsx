import {__} from '@wordpress/i18n';
import SettingsGroup from '@givewp/form-builder/components/canvas/FormSettingsContainer/components/SettingsGroup';
import EmailGeneralSettings from '@givewp/form-builder/settings/group-email-settings/general';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import EmailTemplateOptions from './email/template-options';

export default function FormEmailSettingsGroup() {
    const {emailNotifications} = getFormBuilderWindowData();
    return (
        <SettingsGroup item="item-email-settings" title={__('Email Settings', 'give')}>
            <SettingsGroup item="item-email-settings-general" title={__('General', 'give')}>
                <EmailGeneralSettings />
            </SettingsGroup>

            {emailNotifications.map((emailNotification) => {
                const {id, title} = emailNotification;
                return (
                    <SettingsGroup item={`item-email-settings-${id}`} title={title} key={id}>
                        <EmailTemplateOptions notification={id} />
                    </SettingsGroup>
                );
            })}
        </SettingsGroup>
    );
}
