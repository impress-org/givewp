import {useState} from "react";
import {useFormState} from "@givewp/form-builder/stores/form-state";
import {Button, TextControl} from "@wordpress/components";
import {__} from "@wordpress/i18n";
import {getStorageData} from "@givewp/form-builder/common/getWindowData";

export default ({emailType}) => {

    const [emailAddress, setEmailAddress] = useState<string>('');

    const {settings: {emailTemplateOptions, emailTemplate, emailLogo, emailFromName, emailFromEmail}} = useFormState();

    const {formId, emailPreviewURL} = getStorageData()

    const sendTestEmail = () => {

        // @ts-ignore
        jQuery
            .post({
                // @ts-ignore
                url: emailPreviewURL + '/send',
                headers: {
                    // @ts-ignore
                    'X-WP-Nonce': window.storageData.nonce,
                },
                data: {
                    form_id: formId,
                    email_address: emailAddress,
                    email_type: emailType,
                    email_template: emailTemplate,
                    email_logo: emailLogo,
                    email_from_name: emailFromName,
                    email_from_email: emailFromEmail,
                    ...emailTemplateOptions[emailType]
                },
            })
            .then((response) => {
                alert('email sent')
            })
            .fail((error) => {
                console.log(error)
                alert('error sending email')
            });
    }

    return (
        <>
            <TextControl
                label={__('Email address', 'givewp')}
                help={__('Specify below the email address you want to send a test email to', 'givewp')}
                onChange={setEmailAddress}
                value={emailAddress}
            />
            <Button
                variant={'secondary'}
                onClick={sendTestEmail}
                style={{width:'100%',justifyContent:'center'}}
            >
                {__('Send test email', 'givewp')}
            </Button>
        </>
    )
}
