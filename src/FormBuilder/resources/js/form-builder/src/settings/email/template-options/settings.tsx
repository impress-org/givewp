import {useEffect, useState} from 'react';
import {getFormBuilderData} from '@givewp/form-builder/common/getWindowData';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import {BaseControl, Button, RadioControl, SelectControl, TextControl} from '@wordpress/components';
import {Icon as WPIcon, plus} from '@wordpress/icons';

import {__} from '@wordpress/i18n';
import Editor from './components/editor';
import TrashIcon from '@givewp/form-builder/settings/email/template-options/components/TrashIcon';

type EmailTemplateFieldValues = {
    id: string;
    status: string;
    email_subject: string;
    email_header: string;
    email_message: string;
    email_content_type: string;
    recipient: string[];
};

const EmailTemplateSettings = ({notification, closeModal}) => {
    const [emailTemplateFieldValues, setEmailTemplateFieldValues] = useState<EmailTemplateFieldValues>({
        id: '',
        status: 'customize',
        email_subject: '',
        email_header: '',
        email_message: '',
        email_content_type: '',
        recipient: [''],
    });

    const dispatch = useFormStateDispatch();
    const {emailNotifications, emailDefaultAddress} = getFormBuilderData();

    const {
        settings: {emailTemplateOptions},
    } = useFormState();

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

    useEffect(() => {
        setEmailTemplateFieldValues({
            ...option,
        });
    }, []);

    const updateEmailTemplateField = (property, value) => {
        setEmailTemplateFieldValues((prevValues) => {
            return {
                ...prevValues,
                [property]: value,
            };
        });
    };

    const cancelChanges = () => {
        closeModal();
        setEmailTemplateFieldValues({
            ...option,
        });

        dispatch(
            setFormSettings({
                emailTemplateOptions: {
                    ...emailTemplateOptions,
                    [notification]: option,
                },
            })
        );
    };

    const setEmailTemplateOption = () => {
        closeModal();
        dispatch(
            setFormSettings({
                emailTemplateOptions: {
                    ...emailTemplateOptions,
                    [notification]: {
                        ...option,
                        ...emailTemplateFieldValues,
                    },
                },
            })
        );
    };

    const setEmailTemplateStatus = (property, value) => {
        updateEmailTemplateField('status', value);
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
        <div className={'email-settings-template-container'}>
            <div className={'email-settings-template-container-actions'}>
                <Button
                    style={{
                        padding: 'var(--givewp-spacing-4) var(--givewp-spacing-12)',
                    }}
                    variant={'secondary'}
                    onClick={cancelChanges}
                >
                    {__('Cancel', 'givewp')}
                </Button>
                <Button
                    style={{
                        padding: 'var(--givewp-spacing-4) var(--givewp-spacing-8)',
                    }}
                    variant={'primary'}
                    onClick={setEmailTemplateOption}
                >
                    {__('Set and close', 'givewp')}
                </Button>
            </div>

            <RadioControl
                className="radio-control--email-options"
                label={__('Email options', 'givewp')}
                hideLabelFromVision={true}
                help={__(
                    'Global options are set in GiveWP settings. You may override them for this form here',
                    'givewp'
                )}
                selected={option?.status ?? 'global'}
                options={config.statusOptions}
                onChange={(value) => setEmailTemplateStatus('status', value)}
            />

            {'enabled' === option.status && (
                <>
                    <TextControl
                        label={__('Email Subject', 'givewp')}
                        help={__('Enter the email subject line', 'givewp')}
                        onChange={(value) => updateEmailTemplateField('email_subject', value)}
                        value={emailTemplateFieldValues.email_subject || config.defaultValues.email_subject}
                    />

                    <TextControl
                        label={__('Email Header', 'givewp')}
                        help={__('Enter the email header that appears at the top of the email', 'givewp')}
                        onChange={(value) => updateEmailTemplateField('email_header', value)}
                        // @ts-ignore
                        value={emailTemplateFieldValues.email_header || config.defaultValues.email_header}
                    />

                    <Editor
                        value={
                            emailTemplateFieldValues?.email_message.replace(/\n/g, '<br />') ||
                            config.defaultValues.email_message
                        }
                        onChange={(value) => updateEmailTemplateField('email_message', value)}
                    />

                    <SelectControl
                        className={'select-control--email-options'}
                        onChange={(value) => updateEmailTemplateField('email_content_type', value)}
                        label={__('Email content type', 'givewp')}
                        help={__('Choose email type', 'givewp')}
                        value={emailTemplateFieldValues.email_content_type || config.defaultValues.email_content_type}
                        options={[
                            {label: __('HTML', 'givewp'), value: 'text/html'},
                            {label: __('Plain', 'givewp'), value: 'text/plain'},
                        ]}
                    />

                    {config.supportsRecipients && (
                        <div className={'email-settings-template-recipient'}>
                            <div>
                                <h2 className={'email-settings-header'}>{__('Email recipient', 'givewp')}</h2>
                                <p className={'email-settings-description'}>
                                    {__('Email address that should receive a notification', 'givewp')}
                                </p>
                            </div>
                            <BaseControl id={'give-email-template-recipient'} label={__('Email', 'givewp')}>
                                {recipients.map((recipientEmail, index) => {
                                    return (
                                        <li
                                            key={'level-option-inspector-' + index}
                                            className={'givewp-donation-level-control'}
                                        >
                                            <TextControl
                                                label={__('Donation amount level', 'give')}
                                                hideLabelFromVision
                                                value={recipientEmail}
                                                onChange={(value) => {
                                                    const newRecipients = [...recipients];
                                                    newRecipients[index] = value;
                                                    updateEmailTemplateField('recipient', newRecipients);
                                                }}
                                            />

                                            <Button
                                                className={'email-settings-template-recipient-delete'}
                                                onClick={() => {
                                                    recipients.splice(index, 1);
                                                    updateEmailTemplateField('recipient', recipients.slice());
                                                }}
                                            >
                                                <TrashIcon />
                                            </Button>
                                        </li>
                                    );
                                })}

                                <Button
                                    style={{width: '100%', justifyContent: 'center', gap: '.25rem'}}
                                    variant={'secondary'}
                                    onClick={() => updateEmailTemplateField('recipient', [...recipients, ''])}
                                >
                                    <WPIcon size={17} icon={plus} /> {__('Add email', 'give')}
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
                </>
            )}
        </div>
    );
};

export default EmailTemplateSettings;
