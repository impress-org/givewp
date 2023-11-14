import {Button, PanelRow} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {useRef, useState} from 'react';
import CopyToClipboardButton from './components/copy-to-clipboard-button';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import SendPreviewEmail from './components/send-preview-email';
import EmailPreviewContent from './components/email-preview-content';
import {useFormState} from '@givewp/form-builder/stores/form-state';
import {createInterpolateElement} from '@wordpress/element';
import EmailTemplateSettings from '@givewp/form-builder/settings/group-email-settings/email/template-options/settings';
import SettingsSection from '@givewp/form-builder/components/canvas/FormSettingsContainer/components/SettingsSection';

export default function EmailTemplateOptions({notification}) {
    const [showPreview, setShowPreview] = useState<boolean>(false);
    const templateTagsRef = useRef<HTMLUListElement>(null);

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
            a: <a href="https://givewp.com/documentation/core/settings/emails/email-tags/" target="_blank" />,
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
                        <EmailTemplateSettings notification={notification} templateTagsRef={templateTagsRef} />

                        {selectedNotificationStatus === 'enabled' && (
                            <>
                                <SettingsSection
                                    title={__('Send a test email', 'give')}
                                    description={__(
                                        'Enter the email address you want to send a test email to.',
                                        'give'
                                    )}
                                >
                                    <PanelRow>
                                        <SendPreviewEmail
                                            defaultEmailAddress={emailDefaultAddress}
                                            emailType={notification}
                                        />
                                    </PanelRow>
                                </SettingsSection>

                                <SettingsSection
                                    title={__('Template tags', 'give')}
                                    description={templateTagsDescription}
                                >
                                    <PanelRow>
                                        <ul className={'email-settings-template-tags'} ref={templateTagsRef}>
                                            {emailTemplateTags.map((tag) => (
                                                <li key={tag.tag}>
                                                    <strong>{'{' + tag.tag + '}'}</strong>
                                                    <p style={{fontSize: '.75rem'}}>{tag.desc}</p>
                                                    <CopyToClipboardButton text={'{' + tag.tag + '}'} />
                                                </li>
                                            ))}
                                        </ul>
                                    </PanelRow>
                                </SettingsSection>

                                <Button
                                    className={'email-settings__email-btn email-settings__email-btn--preview'}
                                    variant={'secondary'}
                                    onClick={() => setShowPreview(true)}
                                >
                                    {__('Preview email', 'give')}
                                </Button>
                            </>
                        )}
                    </div>
                </>
            )}
        </>
    );
};
