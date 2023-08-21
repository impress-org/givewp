import {useState} from 'react';
import {useFormState} from '@givewp/form-builder/stores/form-state';
import {Button, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {getStorageData} from '@givewp/form-builder/common/getWindowData';

export default ({emailType, defaultEmailAddress}) => {
    const [emailAddress, setEmailAddress] = useState<string>(defaultEmailAddress);

    const {
        settings: {emailTemplateOptions, emailTemplate, emailLogo, emailFromName, emailFromEmail},
    } = useFormState();

    const {formId, emailPreviewURL} = getStorageData();

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
            <h2 style={{}}>{__('Send a test email', 'givewp')}</h2>
            <p style={{fontSize: '0.75rem', color: 'rgb(117,117,117)'}}>
                {__('Specify below the email address you want to send a test email to', 'givewp')}
            </p>
            {defaultEmailAddress !== null && (
                <TextControl label={__('Email address', 'givewp')} onChange={setEmailAddress} value={emailAddress} />
            )}
            <Button variant={'secondary'} onClick={sendTestEmail} style={{width: '100%', justifyContent: 'center'}}>
                {__('Send test email', 'givewp')}
            </Button>
        </>
    );
};
