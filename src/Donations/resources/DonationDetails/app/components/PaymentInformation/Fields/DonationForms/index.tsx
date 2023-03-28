import {__} from '@wordpress/i18n';
import React from 'react';
import SearchSelector from '@givewp/components/AdminUI/SearchSelector';
import Field from '../Field';

const {forms} = window.GiveDonations;
const {formTitle} = window.GiveDonations.donationDetails;

export default function FormsField() {
    return (
        <Field label={__('Donation form', 'give')}>
            <SearchSelector name={'form'} placeholder={__('Search for a donation form', 'give')} options={forms} />
        </Field>
    );
}
