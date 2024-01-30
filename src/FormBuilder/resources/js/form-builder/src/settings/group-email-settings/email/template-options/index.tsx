import { useEffect, useRef, useState } from "react";
import { createInterpolateElement } from "@wordpress/element";
import { Button, PanelRow } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { SettingsSection } from "@givewp/form-builder-library";
import { getFormBuilderWindowData } from "@givewp/form-builder/common/getWindowData";
import SendPreviewEmail from "./components/send-preview-email";
import EmailPreviewContent from "./components/email-preview-content";
import EmailTemplateSettings from "@givewp/form-builder/settings/group-email-settings/email/template-options/settings";
import TemplateTags from "@givewp/form-builder/components/settings/TemplateTags";

/**
 * @since 3.3.0
 */
export default function EmailTemplateOptions({notification, settings, setSettings}) {
    const [showPreview, setShowPreview] = useState<boolean>(false);
    const templateTagsRef = useRef<HTMLUListElement>(null);

    const {emailTemplateOptions} = settings;

    const selectedNotificationStatus = emailTemplateOptions[notification]?.status ?? 'global';

    const {emailTemplateTags, emailDefaultAddress} = getFormBuilderWindowData();

    const templateTagsDescription = createInterpolateElement(
        __(
            'Available template tags for this email. HTML is accepted. <a>See our documentation</a> for examples of how to use custom meta email tags to output additional donor or donation information in your GiveWP emails',
            'give'
        ),
        {
            a: <a href="https://givewp.com/documentation/core/settings/emails/email-tags/" target="_blank" />,
        }
    );

    useEffect(() => {
        if (showPreview) {
            setShowPreview(false);
        }
    }, [notification]);

    return (
        <>
            {showPreview && (
                <>
                    <EmailPreviewContent emailType={notification} settings={settings} />
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
                        <EmailTemplateSettings
                            notification={notification}
                            settings={settings}
                            setSettings={setSettings}
                            templateTagsRef={templateTagsRef}
                        />

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
                                            settings={settings}
                                        />
                                    </PanelRow>
                                </SettingsSection>

                                <SettingsSection
                                    title={__('Template tags', 'give')}
                                    description={templateTagsDescription}
                                >
                                    <PanelRow>
                                        <TemplateTags
                                            templateTags={emailTemplateTags}
                                            templateTagsRef={templateTagsRef}
                                        />
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
