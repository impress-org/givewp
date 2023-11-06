import {useState} from 'react';
import {useFormState} from '@givewp/form-builder/stores/form-state';
import {Button, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';

type SendPreviewEmailProps = {
    emailType: string;
    defaultEmailAddress: string;
};

export default ({emailType, defaultEmailAddress}: SendPreviewEmailProps) => {
    const [emailAddress, setEmailAddress] = useState<string>(defaultEmailAddress);

    const {
        settings: {emailTemplateOptions, emailTemplate, emailLogo, emailFromName, emailFromEmail},
    } = useFormState();

    const {formId, emailPreviewURL, nonce} = getFormBuilderWindowData();

    const sendTestEmail = () => {
        // @ts-ignore
        jQuery
            .post({
                // @ts-ignore
                url: emailPreviewURL + '/send',
                headers: {
                    'X-WP-Nonce': nonce,
                },
                data: {
                    form_id: formId,
                    email_address: emailAddress,
                    email_type: emailType,
                    email_template: emailTemplate,
                    email_logo: emailLogo,
                    email_from_name: emailFromName,
                    email_from_email: emailFromEmail,
                    ...emailTemplateOptions[emailType],
                },
            })
            .then((response) => {
                alert('email sent');
            })
            .fail((error) => {
                console.log(error);
                alert('error sending email');
            });
    };

    return (
        <>
            {defaultEmailAddress !== null && <TextControl onChange={setEmailAddress} value={emailAddress} />}
            <Button className={'email-settings__email-btn'} variant={'secondary'} onClick={sendTestEmail}>
                {__('Send test email', 'give')}
            </Button>
        </>
    );
};
