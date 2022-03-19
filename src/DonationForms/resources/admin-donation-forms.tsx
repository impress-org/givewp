import {StrictMode} from 'react';
import ReactDOM from 'react-dom';
import {__} from "@wordpress/i18n";
import {donationFormsColumns} from './components/DonationFormsColumns';
import {ListTablePage, ListTableApi} from '@givewp/components';
import './admin-donation-forms.module.scss';

declare global {
    interface Window {
        GiveDonationForms: {apiNonce: string; apiRoot: string};
    }
}

const donationFormsApi = new ListTableApi(window.GiveDonationForms);

const donationStatus = [
    {
        name: 'any',
        text: __('All', 'give'),
    },
    {
        name: 'publish',
        text: __('Published', 'give'),
    },
    {
        name: 'pending',
        text: __('Pending', 'give'),
    },
    {
        name: 'draft',
        text: __('Draft', 'give'),
    },
    {
        name: 'trash',
        text: __('Trash', 'give'),
    }
]

const headerButtons = [
    {
        text: __('Add Form', 'give'),
        link: 'post-new.php?post_type=give_forms',
    }
];

const donationFormsFilters = [
    {
        name: 'search',
        type: 'search',
        text: __('Search by name or ID', 'give'),
        ariaLabel: __('Search donation forms', 'give')
    },
    {
        name: 'status',
        type: 'select',
        text: __('status', 'give'),
        ariaLabel: __('Filter donation forms by status', 'give'),
        options: donationStatus
    }
]

ReactDOM.render(
    <StrictMode>
        <ListTablePage
            title={__('Donation Forms', 'give')}
            singleName={__('donation form', 'give')}
            pluralName={__('donation forms', 'give')}
            headerButtons={headerButtons}
            filters={donationFormsFilters}
            columns={donationFormsColumns}
            api={donationFormsApi}
        />
    </StrictMode>,
    document.getElementById('give-admin-donation-forms-root')
);
