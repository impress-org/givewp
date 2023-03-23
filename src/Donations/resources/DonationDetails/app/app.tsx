import React from 'react';

import FormPage from '@givewp/components/AdminUI/FormPage';
import FormTemplate from './components/FormTemplate';

import {validationSchema} from './config/schema';
import {endpoint} from './config/data';
import {pageInformation} from './config/pageInformation';

import {defaultFormValues} from './utilities/defaultFormValues';
import {actions} from './utilities/actions';

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
            actionConfig={actions}
        >
            <FormTemplate />
        </FormPage>
    );
}
