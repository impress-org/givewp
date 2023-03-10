import React from 'react';

import {__} from '@wordpress/i18n';
import FormPage from '@givewp/components/AdminUI/FormPage';
import FormTemplate from './components/FormTemplate';

import {validationSchema} from '../schema';

import './css/style.scss';

import testData from './data';

/**
 *
 * @unreleased
 */

export default function App() {
    const data = testData();
    const defaultValues = {totalDonation: data.amount, feeAmount: data.feeAmountRecovered, time: data.createdAt};

    const handleSubmitRequest = (formValues) => {
        event.preventDefault();
        console.log(JSON.stringify(formValues));
        alert(`post request submitted. Form data = ${JSON.stringify(formValues)}`);
    };

    const actionConfig = [
        {title: __('Refund donation', 'give'), action: () => alert('refund donation')},
        {title: __('Download receipt', 'give'), action: () => alert('Download receipt')},
        {title: __('Refund donation', 'give'), action: () => alert('Refund donation')},
        {title: __('Delete donation', 'give'), action: () => alert('Delete donation')},
    ];

    return (
        <FormPage
            formId={'givewp-donation-detail-page'}
            defaultValues={defaultValues}
            validationSchema={validationSchema}
            handleSubmitRequest={handleSubmitRequest}
            pageDetails={{
                id: 100,
                description: __('Donation ID', 'give'),
                title: __('Donation', 'give'),
            }}
            navigationalOptions={[
                {id: 1, title: 'donation 1'},
                {id: 2, title: 'donation 2'},
                {id: 3, title: 'donation 3'},
            ]}
            actionConfig={actionConfig}
        >
            <FormTemplate data={data} />
        </FormPage>
    );
}
