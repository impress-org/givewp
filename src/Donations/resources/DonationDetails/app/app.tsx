import React from 'react';

import FormPage from '@givewp/components/AdminUI/FormPage';
import FormTemplate from './components/FormTemplate';

import {actionConfig, defaultFormValues, endpoint, navigationalOptions, pageInformation} from './config';

import {validationSchema} from './schema';

import './css/style.scss';

/**
 *
 * @unreleased
 */

export default function App() {
    return (
        <FormPage
            formId={'givewp-donation-detail-page'}
            endpoint={endpoint}
            defaultValues={defaultFormValues}
            validationSchema={validationSchema}
            pageInformation={pageInformation}
            navigationalOptions={navigationalOptions}
            actionConfig={actionConfig}
        >
            <FormTemplate />
        </FormPage>
    );
}
