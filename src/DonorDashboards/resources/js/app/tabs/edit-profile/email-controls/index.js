import {Fragment} from 'react';
import {__} from '@wordpress/i18n';

import TextControl from '../../../components/text-control';
import FieldRow from '../../../components/field-row';
import Button from '../../../components/button';

const EmailControls = ({primaryEmail, additionalEmails, onChangePrimaryEmail, onChangeAdditionalEmails}) => {
    const setAdditionalEmail = (newEmail, index) => {
        const newAdditionalEmails = additionalEmails.concat();
        newAdditionalEmails[index] = newEmail;
        onChangeAdditionalEmails(newAdditionalEmails);
    };

    const removeAdditionalEmail = (remove) => {
        const newAdditionalEmails = additionalEmails.filter((email, index) => index !== remove);
        onChangeAdditionalEmails(newAdditionalEmails);
    };

    const addAdditionalEmail = (newEmail) => {
        const newAdditionalEmails = additionalEmails.concat(newEmail);
        onChangeAdditionalEmails(newAdditionalEmails);
    };

    const setPrimaryEmail = (newEmail) => {
        onChangePrimaryEmail(newEmail);
    };

    const makePrimaryEmail = async (newEmail, index) => {
        const oldPrimaryEmail = primaryEmail;
        setPrimaryEmail(newEmail);
        setAdditionalEmail(oldPrimaryEmail, index);
    };

    const additionalEmailControls = additionalEmails.map((email, index) => {
        return (
            <FieldRow key={index}>
                <TextControl
                    label={index === 0 ? __('Additional Emails', 'give') : null}
                    value={email}
                    onChange={(value) => setAdditionalEmail(value, index)}
                    icon="envelope"
                />
                <div className="give-donor-dashboard__email-controls">
                    <div
                        className="give-donor-dashboard__make-primary-email"
                        onClick={() => makePrimaryEmail(email, index)}
                    >
                        {__('Make Primary', 'give')}
                    </div>
                    |
                    <div className="give-donor-dashboard__delete-email" onClick={() => removeAdditionalEmail(index)}>
                        {__('Delete', 'give')}
                    </div>
                </div>
            </FieldRow>
        );
    });

    return (
        <Fragment>
            <TextControl
                label={__('Primary Email', 'give')}
                value={primaryEmail}
                onChange={(value) => setPrimaryEmail(value)}
                icon="envelope"
            />
            {additionalEmailControls}
            <Button onClick={() => addAdditionalEmail('')} icon="plus">
                {__('Add Email', 'give')}
            </Button>
        </Fragment>
    );
};
export default EmailControls;
