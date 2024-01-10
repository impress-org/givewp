import {useEffect, useState} from 'react';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import {__} from '@wordpress/i18n';

type EmailPreviewContentProps = {
    emailType: string;
    settings: any;
};

const EmailPreviewContent = ({emailType, settings}: EmailPreviewContentProps) => {
    const [previewHtml, setPreviewHtml] = useState<string>(null);

    const {emailTemplateOptions, emailTemplate, emailLogo, emailFromName, emailFromEmail} = settings;

    const {formId, nonce, emailPreviewURL} = getFormBuilderWindowData();

    useEffect(() => {
        // @ts-ignore
        jQuery
            .post({
                // @ts-ignore
                url: emailPreviewURL + '/show?query', // Query param added to prevent an undefined index warning in the legacy code.
                headers: {
                    // @ts-ignore
                    'X-WP-Nonce': nonce,
                },
                data: {
                    form_id: formId,
                    email_type: emailType,
                    email_template: emailTemplate,
                    email_logo: emailLogo,
                    email_from_name: emailFromName,
                    email_from_email: emailFromEmail,
                    ...emailTemplateOptions[emailType],
                },
            })
            .then((response) => {
                setPreviewHtml(response);
            })
            .fail((error) => {
                setPreviewHtml('Error loading preview.');
            });
    }, []);

    return previewHtml ? (
        <iframe
            srcDoc={previewHtml}
            className={'email-settings-preview-iframe'}
            style={{width: '100%', height: '100%', border: 'none'}}
        />
    ) : (
        <div className={'email-settings-preview-generating'}>{__('Generating preview...', 'give')}</div>
    );
};

export default EmailPreviewContent;
