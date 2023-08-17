import {useEffect, useState} from "react";
import {useFormState} from "@givewp/form-builder/stores/form-state";
import {getStorageData} from "@givewp/form-builder/common/getWindowData";
import {__} from "@wordpress/i18n";

const EmailPreviewContent = ({emailType}) => {

    const [ previewHtml, setPreviewHtml ] = useState<string>(null);

    const {settings: {emailTemplateOptions, emailTemplate, emailLogo, emailFromName, emailFromEmail}} = useFormState();

    const {formId, nonce, emailPreviewURL} = getStorageData()

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
                    ...emailTemplateOptions[emailType]
                },
            })
            .then((response) => {
                setPreviewHtml(response)
            })
            .fail((error) => {
                setPreviewHtml('Error loading preview.')
            });
    }, []);

    return previewHtml
        ? <iframe
            srcDoc={previewHtml}
            style={{width:'100%',height:'100%',border:'none'}}
        />
        : <div style={{
            width: '100%',
            height: '100%',
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center'
        }}>
            {__('Generating preview...', 'give')}
        </div>;
}

export default EmailPreviewContent;
