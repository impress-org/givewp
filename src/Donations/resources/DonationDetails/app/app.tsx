import React from 'react';

import FormPage from '@givewp/components/AdminUI/FormPage';
import FormTemplate from './components/FormTemplate';
import {validationSchema} from '../schema';

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
            id={'givewp-donation-detail-page'}
            defaultValues={defaultValues}
            validationSchema={validationSchema}
            handleSubmitRequest={handleSubmitRequest}
        >
            <FormTemplate defaultValues={defaultValues} />
        </FormPage>
    );
}
