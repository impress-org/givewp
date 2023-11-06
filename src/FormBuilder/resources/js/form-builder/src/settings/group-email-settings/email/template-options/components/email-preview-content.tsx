import {useEffect, useState} from 'react';
import {useFormState} from '@givewp/form-builder/stores/form-state';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import {__} from '@wordpress/i18n';

type EmailPreviewContentProps = {
    emailType: string;
};

const EmailPreviewContent = ({emailType}: EmailPreviewContentProps) => {
    const [previewHtml, setPreviewHtml] = useState<string>(null);

    const {
        settings: {emailTemplateOptions, emailTemplate, emailLogo, emailFromName, emailFromEmail},
    } = useFormState();

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
