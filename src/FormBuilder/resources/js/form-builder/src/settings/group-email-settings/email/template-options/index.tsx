import {Button} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {useState} from 'react';
import CopyToClipboardButton from './components/copy-to-clipboard-button';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import SendPreviewEmail from './components/send-preview-email';
import EmailPreviewContent from './components/email-preview-content';
import {useFormState} from '@givewp/form-builder/stores/form-state';
import {createInterpolateElement} from '@wordpress/element';
import EmailTemplateSettings from '@givewp/form-builder/settings/group-email-settings/email/template-options/settings';

export default function EmailTemplateOptions({notification}) {
    const [showPreview, setShowPreview] = useState<boolean>(false);

    const {
        settings: {emailTemplateOptions},
    } = useFormState();

    const selectedNotificationStatus = emailTemplateOptions[notification]?.status ?? 'global';

    const {emailTemplateTags, emailNotifications, emailDefaultAddress} = getFormBuilderWindowData();

    const templateTagsDescription = createInterpolateElement(
        __(
            'Available template tags for this email. HTML is accepted. <a>See our documentation</a> for examples of how to use custom meta email tags to output additional donor or donation information in your GiveWP emails',
            'give'
        ),
        {
            a: <a href="https://make.wordpress.org" target="_blank" />,
        }
    );

    return (
        <>
            {showPreview && (
                <>
                    <EmailPreviewContent emailType={notification} />
                    <Button
                        className={'email-preview__back-btn'}
                        variant={'secondary'}
                        onClick={() => setShowPreview(false)}
                    >
                        {__('Back to template settings', 'give')}
                    </Button>
                </>
            )}

            {!showPreview && (
                <>
                    <div className={'email-settings'}>
                        <h2 className={'email-settings__header'}>{__('Notification', 'give')}</h2>
                        <EmailTemplateSettings notification={notification} />

                        <div style={{visibility: selectedNotificationStatus === 'enabled' ? 'visible' : 'hidden'}}>
                            <div>
                                <h2 className={'email-settings__header'}>{__('Preview email', 'give')}</h2>
                                <p className={'email-settings__description'}>
                                    {__('Preview the email message in your browser', 'give')}
                                </p>
                                <Button
                                    className={'email-settings__email-btn'}
                                    variant={'secondary'}
                                    onClick={() => setShowPreview(true)}
                                >
                                    {__('Preview email', 'give')}
                                </Button>
                            </div>
                            <div>
                                <SendPreviewEmail defaultEmailAddress={emailDefaultAddress} emailType={notification} />
                            </div>
                            <div>
                                <h2 className={'email-settings__header'}>{__('Template tags', 'give')}</h2>
                                <p className={'email-settings__description'}>{templateTagsDescription}</p>
                                <ul className={'email-settings-template-tags'}>
                                    {emailTemplateTags.map((tag) => (
                                        <li key={tag.tag}>
                                            <strong>{'{' + tag.tag + '}'}</strong>
                                            <p style={{fontSize: '.75rem'}}>{tag.desc}</p>
                                            <CopyToClipboardButton text={'{' + tag.tag + '}'} />
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </div>
                    </div>
                </>
            )}
        </>
    );
};
