import {__} from '@wordpress/i18n';
import {data} from '../../../../config/data';
import React from 'react';
import SearchSelector from '@givewp/components/AdminUI/SearchSelector';
import Field from '../Field';

const options = [
    {value: 1, label: 'donation form 1'},
    {value: 2, label: 'donation form 2'},
    {value: 3, label: 'donation form 3'},
    {value: 4, label: 'donation form 4'},
];

export default function FormsField() {
    return (
        <Field label={__('Donation form', 'give')}>
            <SearchSelector
                name={'form'}
                placeholder={__('Search for a donation form', 'give')}
                options={options}
                defaultLabel={data.formTitle}
            />
        </Field>
    );
}
