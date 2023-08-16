import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import {BaseControl, Button, RadioControl, SelectControl, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import Editor from './components/editor';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import DeleteButton from '@givewp/form-builder/blocks/fields/amount/inspector/delete-button';

const EmailTemplateSettings = ({notification}) => {
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
        <div
            style={{
                display: 'flex',
                flexDirection: 'column',
                gap: 'var(--givewp-spacing-6)',
                marginBottom: '20px', // Prevent clipping
            }}
        >
            <RadioControl
                className="radio-control--email-options"
                label={__('Email options', 'givewp')}
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
                        label={__('Email Subject', 'givewp')}
                        help={__('Enter the email subject line', 'givewp')}
                        onChange={(value) => updateEmailTemplateOption('email_subject', value)}
                        value={option.email_subject || config.defaultValues.email_subject}
                    />

                    <TextControl
                        label={__('Email Header', 'givewp')}
                        help={__('Enter the email header that appears at the top of the email', 'givewp')}
                        onChange={(value) => updateEmailTemplateOption('email_header', value)}
                        // @ts-ignore
                        value={option.email_header || config.defaultValues.email_header}
                    />

                    <Editor
                        value={option?.email_message.replace(/\n/g, '<br />') || config.defaultValues.email_message}
                        onChange={(value) => updateEmailTemplateOption('email_message', value)}
                    />

                    <SelectControl
                        onChange={(value) => updateEmailTemplateOption('email_content_type', value)}
                        label={__('Email content type', 'givewp')}
                        help={__('Choose email type', 'givewp')}
                        value={option.email_content_type || config.defaultValues.email_content_type}
                        options={[
                            {label: __('HTML', 'givewp'), value: 'text/html'},
                            {label: __('Plain', 'givewp'), value: 'text/plain'},
                        ]}
                    />

                    {config.supportsRecipients && (
                        <>
                            <BaseControl
                                id={'give-email-template-recipient'}
                                label={__('Email', 'givewp')}
                                help={__('Enter the email address that should receive a notification.', 'givewp')}
                            >
                                {recipients.map((recipientEmail, index) => {
                                    return (
                                        <li
                                            key={'level-option-inspector-' + index}
                                            style={{
                                                display: 'flex',
                                                gap: '16px',
                                                justifyContent: 'space-between',
                                                alignItems: 'flex-end',
                                            }}
                                            className={'givewp-donation-level-control'}
                                        >
                                            <TextControl
                                                label={__('Donation amount level', 'give')}
                                                hideLabelFromVision
                                                value={recipientEmail}
                                                onChange={(value) => {
                                                    const newRecipients = [...recipients];
                                                    newRecipients[index] = value;
                                                    updateEmailTemplateOption('recipient', newRecipients);
                                                }}
                                            />
                                            <DeleteButton
                                                onClick={() => {
                                                    recipients.splice(index, 1);
                                                    updateEmailTemplateOption('recipient', recipients.slice());
                                                }}
                                            />
                                        </li>
                                    );
                                })}
                                <Button
                                    variant={'tertiary'}
                                    onClick={() => updateEmailTemplateOption('recipient', [...recipients, ''])}
                                >
                                    Add email
                                </Button>
                            </BaseControl>
                        </>
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
                </>
            )}
        </div>
    );
};

export default EmailTemplateSettings;
