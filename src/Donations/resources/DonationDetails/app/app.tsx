import React from 'react';
import {__} from '@wordpress/i18n';
import FormPage from '@givewp/components/AdminUI/FormPage';
import FormTemplate from './components/FormTemplate';
import {validationSchema} from '../schema';

import './css/style.scss';

/**
 *
 * @unreleased
 */

export default function App() {
    const defaultValues = {};

    const handleSubmitRequest = (formValues) => {
        console.log(JSON.stringify(formValues));
        alert(`post request submitted. Form data = ${JSON.stringify(formValues)}`);
    };

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
        >
            <FormTemplate />
        </FormPage>
    );
}
