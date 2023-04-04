import React from 'react';

import {__} from '@wordpress/i18n';

import FormPage from '@givewp/components/AdminUI/FormPage';
import FormTemplate from './components/FormTemplate';

import {validationSchema} from './config/schema';
import {apiNonce, endpoint} from '../../window';
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
            apiNonce={apiNonce}
            successMessage={__('Donation details have been updated successfully', 'give')}
            errorMessage={__(
                'Error: The Donation details were unable to update successfully. Please try again.',
                'give'
            )}
        >
            <FormTemplate />
        </FormPage>
    );
}
