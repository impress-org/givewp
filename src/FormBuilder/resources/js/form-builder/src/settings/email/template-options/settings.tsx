import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import {BaseControl, Button, RadioControl, SelectControl, TextControl} from '@wordpress/components';
import {Icon as WPIcon, plus} from '@wordpress/icons';

import {__} from '@wordpress/i18n';
import Editor from '@givewp/form-builder/components/editor';
import TrashIcon from '@givewp/form-builder/settings/email/template-options/components/TrashIcon';

type EmailTemplateSettingsProps = {
    notification: string;
};

const EmailTemplateSettings = ({notification}: EmailTemplateSettingsProps) => {
    const dispatch = useFormStateDispatch();
    const {
        settings: {emailTemplateOptions},
    } = useFormState();

    const {emailNotifications, emailDefaultAddress} = getFormBuilderWindowData();
    const config = emailNotifications.find((config) => config.id === notification);

    const option = {
        status: config.defaultValues.notification ?? 'global',
        email_subject: config.defaultValues.email_subject,
        email_header: config.defaultValues.email_header,
        email_message: config.defaultValues.email_message,
        email_content_type: config.defaultValues.email_content_type,
        recipient: [emailDefaultAddress],
        ...emailTemplateOptions[notification],
    };

    const recipients = option.recipient ?? [''];

    const updateEmailTemplateOption = (property, value) => {
        dispatch(
            setFormSettings({
                emailTemplateOptions: {
                    ...emailTemplateOptions,
                    [notification]: {
                        ...option,
                        [property]: value,
                    },
                },
            })
        );
    };

    return (
        <div className={'email-settings-template__container'}>
            <RadioControl
                className="radio-control--email-options"
                label={__('Email options', 'give')}
                hideLabelFromVision={true}
                help={__(
                    'Global options are set in GiveWP settings. You may override them for this form here',
                    'givewp'
                )}
                selected={option.status ?? 'global'}
                options={config.statusOptions}
                onChange={(value) => updateEmailTemplateOption('status', value)}
            />

            {'enabled' === option.status && (
                <>
                    <TextControl
                        label={__('Email Subject', 'give')}
                        help={__('Enter the email subject line', 'give')}
                        onChange={(value) => updateEmailTemplateOption('email_subject', value)}
                        value={option.email_subject || config.defaultValues.email_subject}
                    />

                    <TextControl
                        label={__('Email Header', 'give')}
                        help={__('Enter the email header that appears at the top of the email', 'give')}
                        onChange={(value) => updateEmailTemplateOption('email_header', value)}
                        // @ts-ignore
                        value={option.email_header || config.defaultValues.email_header}
                    />

                    <Editor
                        value={option?.email_message.replace(/\n/g, '<br />') || config.defaultValues.email_message}
                        onChange={(value) => updateEmailTemplateOption('email_message', value)}
                    />

                    <SelectControl
                        className={'select-control--email-options'}
                        onChange={(value) => updateEmailTemplateOption('email_content_type', value)}
                        label={__('Email content type', 'give')}
                        help={__('Choose email type', 'give')}
                        value={option.email_content_type || config.defaultValues.email_content_type}
                        options={[
                            {label: __('HTML', 'give'), value: 'text/html'},
                            {label: __('Plain', 'give'), value: 'text/plain'},
                        ]}
                    />

                    {config.supportsRecipients && (
                        <div className={'email-settings-template__recipient'}>
                            <div>
                                <h2 className={'email-settings__header'}>{__('Email recipient', 'give')}</h2>
                                <p className={'email-settings__description'}>
                                    {__('Email address that should receive a notification', 'give')}
                                </p>
                            </div>
                            <BaseControl id={'give-email-template-recipient'} label={__('Email', 'give')}>
                                {recipients.map((recipientEmail, index) => {
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
                                    <WPIcon size={17} icon={plus} /> {__('Add email', 'give')}
                                </Button>
                            </BaseControl>
                        </div>
                    )}
                    {!config.supportsRecipients && (
                        <TextControl
                            disabled={true}
                            label={__('Email', 'give')}
                            help={__(
                                'This email is automatically sent to the individual fundraiser and the recipient cannot be customized.',
                                'givewp'
                            )}
                            onChange={() => null}
                            value="{donor_email}"
                        />
                    )}
                </>
            )}
        </div>
    );
};

export default EmailTemplateSettings;
