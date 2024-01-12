import {__} from '@wordpress/i18n';
import {BaseControl, Button, PanelRow, RadioControl, SelectControl, TextControl} from '@wordpress/components';
import {Icon as WPIcon, plus} from '@wordpress/icons';
import {ClassicEditor, SettingsSection} from '@givewp/form-builder-library';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';

import TrashIcon from './components/TrashIcon';

/**
 * @since 3.3.0
 */
type EmailTemplateSettingsProps = {
    notification: string;
    templateTagsRef: {
        current: HTMLUListElement;
    };
    settings: any;
    setSettings: (props: {}) => void;
};

/**
 * @since 3.3.0
 */
const EmailTemplateSettings = ({notification, templateTagsRef, settings, setSettings}: EmailTemplateSettingsProps) => {
    const {emailTemplateOptions} = settings;

    const {emailNotifications, emailDefaultAddress} = getFormBuilderWindowData();
    const config = emailNotifications.find((config) => config.id === notification);

    const option = {
        status: config.defaultValues.notification ?? 'global',
        email_subject: config.defaultValues.email_subject,
        email_header: config.defaultValues.email_header,
        email_message: config.defaultValues.email_message.replace(/\n/g, '<br />'),
        email_content_type: config.defaultValues.email_content_type,
        recipient: [emailDefaultAddress],
        ...emailTemplateOptions[notification],
    };

    const recipients = option.recipient ? Object.values(option.recipient) : [''];

    const updateEmailTemplateOption = (property, value) => {
        setSettings({
            emailTemplateOptions: {
                ...emailTemplateOptions,
                [notification]: {
                    ...option,
                    [property]: value,
                },
            },
        });
    };
    return (
        <div className={'email-settings-template__container'}>
            <SettingsSection
                title={__('Email notification', 'give')}
                description={__(
                    'Global options are set in GiveWP settings. You may override them for this form here.',
                    'give'
                )}
            >
                <PanelRow>
                    <RadioControl
                        className="radio-control--email-options"
                        label={__('Email options', 'givewp')}
                        hideLabelFromVision={true}
                        selected={option.status ?? 'global'}
                        options={config.statusOptions}
                        onChange={(value) => updateEmailTemplateOption('status', value)}
                    />
                </PanelRow>
            </SettingsSection>

            {'enabled' === option.status && (
                <>
                    <SettingsSection
                        title={__('Template details', 'give')}
                        description={__('Set the content structure for the email', 'give')}
                    >
                        <PanelRow>
                            <TextControl
                                label={__('Email Subject', 'givewp')}
                                help={__('Enter the email subject line', 'givewp')}
                                onChange={(value) => updateEmailTemplateOption('email_subject', value)}
                                value={option.email_subject || config.defaultValues.email_subject}
                            />
                        </PanelRow>

                        <PanelRow>
                            <TextControl
                                label={__('Email Header', 'givewp')}
                                help={__('Enter the email header that appears at the top of the email', 'givewp')}
                                onChange={(value) => updateEmailTemplateOption('email_header', value)}
                                // @ts-ignore
                                value={option.email_header || config.defaultValues.email_header}
                            />
                        </PanelRow>

                        <PanelRow>
                            <SelectControl
                                className={'select-control--email-options'}
                                onChange={(value) => updateEmailTemplateOption('email_content_type', value)}
                                label={__('Email content type', 'givewp')}
                                help={__('Choose email type', 'givewp')}
                                value={option.email_content_type || config.defaultValues.email_content_type}
                                options={[
                                    {label: __('HTML', 'givewp'), value: 'text/html'},
                                    {label: __('Plain', 'givewp'), value: 'text/plain'},
                                ]}
                            />
                        </PanelRow>

                        <PanelRow>
                            <div style={{width: '100%'}}>
                                <ClassicEditor
                                    key={'give-email-template-message__' + notification}
                                    id={'give-email-template-message__' + notification}
                                    label={__('Email message', 'give')}
                                    content={option.email_message}
                                    setContent={(value) => updateEmailTemplateOption('email_message', value)}
                                    rows={10}
                                />
                                <Button
                                    variant={'secondary'}
                                    onClick={() => templateTagsRef.current.scrollIntoView({behavior: 'smooth'})}
                                    style={{
                                        width: '100%',
                                        marginTop: '0.5rem',
                                        height: '2.5rem',
                                        justifyContent: 'center',
                                    }}
                                >
                                    {__('View template tags', 'give')}
                                </Button>
                            </div>
                        </PanelRow>
                    </SettingsSection>
                    <SettingsSection
                        title={__('Email recipient', 'give')}
                        description={__('Email address that should receive a notification', 'give')}
                    >
                        {config.supportsRecipients && (
                            <div className={'email-settings-template__recipient'}>
                                <BaseControl id={'give-email-template-recipient'} label={__('Email', 'givewp')}>
                                    {recipients.map((recipientEmail: string, index) => {
                                        return (
                                            <li
                                                key={'level-option-inspector-' + index}
                                                className={'base-control--email-options'}
                                            >
                                                <TextControl
                                                    hideLabelFromVision
                                                    value={recipientEmail}
                                                    onChange={(value) => {
                                                        const newRecipients = [...recipients];
                                                        newRecipients[index] = value;
                                                        updateEmailTemplateOption('recipient', newRecipients);
                                                    }}
                                                />

                                                <Button
                                                    className={'email-settings-template__recipient-delete-btn'}
                                                    onClick={() => {
                                                        recipients.splice(index, 1);
                                                        updateEmailTemplateOption('recipient', recipients.slice());
                                                    }}
                                                >
                                                    <TrashIcon />
                                                </Button>
                                            </li>
                                        );
                                    })}

                                    <Button
                                        className={'email-settings-template__recipient-add-email-btn'}
                                        variant={'secondary'}
                                        onClick={() => updateEmailTemplateOption('recipient', [...recipients, ''])}
                                    >
                                        <WPIcon size={17} icon={plus} /> {__('Add email', 'givewp')}
                                    </Button>
                                </BaseControl>
                            </div>
                        )}
                        {!config.supportsRecipients && (
                            <TextControl
                                disabled={true}
                                label={__('Email', 'givewp')}
                                help={__(
                                    'This email is automatically sent to the individual fundraiser and the recipient cannot be customized.',
                                    'givewp'
                                )}
                                onChange={() => null}
                                value="{donor_email}"
                            />
                        )}
                    </SettingsSection>
                </>
            )}
        </div>
    );
};

export default EmailTemplateSettings;
