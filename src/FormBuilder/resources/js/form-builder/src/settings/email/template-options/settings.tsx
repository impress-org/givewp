import {BaseControl, Button, RadioControl, SelectControl, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import Editor from './components/editor';
import DeleteButton from '@givewp/form-builder/blocks/fields/amount/inspector/delete-button';

const EmailTemplateSettings = ({setEmailTemplateFieldValues, emailTemplateFieldValues, option, config}) => {
    const recipients = option.recipient ?? [''];

    const updateEmailTemplateFields = (property, value) => {
        setEmailTemplateFieldValues({
            ...option,
            [property]: value,
        });
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
                selected={emailTemplateFieldValues.status ?? 'global'}
                options={option?.statusOptions}
                onChange={(value) => updateEmailTemplateFields('status', value)}
            />

            {'enabled' === option.status && (
                <>
                    <TextControl
                        label={__('Email Subject', 'givewp')}
                        help={__('Enter the email subject line', 'givewp')}
                        onChange={(value) => updateEmailTemplateFields('email_subject', value)}
                        value={emailTemplateFieldValues.email_subject || config.defaultValues.email_subject}
                    />

                    <TextControl
                        label={__('Email Header', 'givewp')}
                        help={__('Enter the email header that appears at the top of the email', 'givewp')}
                        onChange={(value) => updateEmailTemplateFields('email_header', value)}
                        // @ts-ignore
                        value={emailTemplateFieldValues.email_header || config.defaultValues.email_header}
                    />

                    <Editor
                        value={
                            emailTemplateFieldValues?.email_message?.replace(/\n/g, '<br />') ||
                            config.defaultValues.email_message
                        }
                        onChange={(value) => updateEmailTemplateFields('email_message', value)}
                    />

                    <SelectControl
                        onChange={(value) => updateEmailTemplateFields('email_content_type', value)}
                        label={__('Email content type', 'givewp')}
                        help={__('Choose email type', 'givewp')}
                        value={emailTemplateFieldValues.email_content_type || config.defaultValues.email_content_type}
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
                                                    updateEmailTemplateFields('recipient', newRecipients);
                                                }}
                                            />
                                            <DeleteButton
                                                onClick={() => {
                                                    recipients.splice(index, 1);
                                                    updateEmailTemplateFields('recipient', recipients.slice());
                                                }}
                                            />
                                        </li>
                                    );
                                })}
                                <Button
                                    variant={'tertiary'}
                                    onClick={() => updateEmailTemplateFields('recipient', [...recipients, ''])}
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
