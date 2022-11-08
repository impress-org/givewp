import {__} from '@wordpress/i18n';
import {ListTableApi, ListTablePage} from '@givewp/components';
import {DonorsRowActions} from './DonorsRowActions';
import {BulkActionsConfig, FilterConfig} from '@givewp/components/ListTable/ListTablePage';
import styles from '@givewp/components/ListTable/ListTablePage/ListTablePage.module.scss';
import {Interweave} from 'interweave';

declare global {
    interface Window {
        GiveDonors: {
            apiNonce: string;
            apiRoot: string;
            forms: Array<{value: string; text: string}>;
            table: {columns: Array<object>};
        };
    }
}

const API = new ListTableApi(window.GiveDonors);

const donorsFilters: Array<FilterConfig> = [
    {
        name: 'search',
        type: 'search',
        inlineSize: '14rem',
        text: __('Name, Email, or Donor ID', 'give'),
        ariaLabel: __('Search donors', 'give'),
    },
    {
        name: 'form',
        type: 'formselect',
        text: __('All Donation Forms', 'give'),
        ariaLabel: __('Filter donation forms by status', 'give'),
        options: window.GiveDonors.forms,
    },
];

const donorsBulkActions: Array<BulkActionsConfig> = [
    {
        label: __('Delete', 'give'),
        value: 'delete',
        type: 'danger',
        action: async (selected) => {
            const deleteDonations = document.querySelector('#giveDonorsTableDeleteDonations') as HTMLInputElement;
            const args = {ids: selected.join(','), deleteDonationsAndRecords: deleteDonations.checked};
            const response = await API.fetchWithArgs('/delete', args, 'DELETE');
            return response;
        },
        confirm: (selected, names) => (
            <>
                <p>{__('Really delete the following donors?', 'give')}</p>
                <ul role="document" tabIndex={0}>
                    {selected.map((id, index) => (
                        <li key={id}>
                            <Interweave content={names[index]} />
                        </li>
                    ))}
                </ul>
                <div>
                    <input id="giveDonorsTableDeleteDonations" type="checkbox" defaultChecked={true} />
                    <label htmlFor="giveDonorsTableDeleteDonations">
                        {__('Delete all associated donations and records', 'give')}
                    </label>
                </div>
            </>
        ),
    },
];

export default function DonorsListTable() {
    return (
        <ListTablePage
            title={__('Donors', 'give')}
            singleName={__('donors', 'give')}
            pluralName={__('donors', 'give')}
            rowActions={DonorsRowActions}
            bulkActions={donorsBulkActions}
            apiSettings={window.GiveDonors}
            filterSettings={donorsFilters}
        >
            <button className={styles.addFormButton} onClick={showLegacyDonors}>
                {__('Switch to Legacy View', 'give')}
            </button>
        </ListTablePage>
    );
}

const showLegacyDonors = async (event) => {
    await API.fetchWithArgs('/view', {isLegacy: 1});
    window.location.reload();
};
