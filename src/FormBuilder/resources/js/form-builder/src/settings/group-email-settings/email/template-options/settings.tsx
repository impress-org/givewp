import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import {BaseControl, Button, RadioControl, SelectControl, TextControl} from '@wordpress/components';
import {Icon as WPIcon, plus} from '@wordpress/icons';

import {__} from '@wordpress/i18n';
import ClassicEditor from '@givewp/form-builder/components/ClassicEditor';
import TrashIcon from './components/TrashIcon';
import SettingsSection from '@givewp/form-builder/components/canvas/FormSettingsContainer/components/SettingsSection';

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
        email_message: config.defaultValues.email_message.replace(/\n/g, '<br />'),
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
            <SettingsSection
                title={__('Email notification', 'give')}
                description={__(
                    'Global options are set in GiveWP settings. You may override them for this form here.',
                    'give'
                )}
            >
                <div className={'givewp-form-settings__section__body__extra-gap'}>
                    <RadioControl
                        className="radio-control--email-options"
                        label={__('Email options', 'givewp')}
                        hideLabelFromVision={true}
                        selected={option.status ?? 'global'}
                        options={config.statusOptions}
                        onChange={(value) => updateEmailTemplateOption('status', value)}
                    />
                </div>
            </SettingsSection>

            {'enabled' === option.status && (
                <>
                    <SettingsSection
                        title={__('Template details', 'give')}
                        description={__('Set the content structure for the email', 'give')}
                    >
                        <div className={'givewp-form-settings__section__body__extra-gap'}>
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

                        <ClassicEditor
                            id={'givewp-custom-email-message'}
                            label={__('Email Message', 'give')}
                            content={option.email_message}
                            setContent={(value) => updateEmailTemplateOption('email_message', value)}
                        />

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
                        </div>
                    </SettingsSection>
                    <SettingsSection
                        title={__('Email recipient', 'give')}
                        description={__('Email address that should receive a notification', 'give')}
                    >
                        <div className={'givewp-form-settings__section__body__extra-gap'}>
                            {config.supportsRecipients && (
                                <div className={'email-settings-template__recipient'}>
                                    <BaseControl id={'give-email-template-recipient'} label={__('Email', 'givewp')}>
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
                        </div>
                    </SettingsSection>
                </>
            )}
        </div>
    );
};

export default EmailTemplateSettings;
