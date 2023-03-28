import {__} from '@wordpress/i18n';
import React from 'react';
import SearchSelector from '@givewp/components/AdminUI/SearchSelector';
import Field from '../Field';

const {forms} = window.GiveDonations;

export default function DonationFormsField() {
    const formattedOptions = formatOptions(forms);

    return (
        <Field label={__('Donation form', 'give')}>
            <SearchSelector
                name={'formId'}
                placeholder={__('Search for a donation form', 'give')}
                options={formattedOptions}
            />
        </Field>
    );
}

function formatOptions(options) {
    return options.map((object) => ({label: object.text, value: parseInt(object.value)}));
}
