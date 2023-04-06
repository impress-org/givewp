import {__} from '@wordpress/i18n';
import {useSWRConfig} from 'swr';
import RowAction from '@givewp/components/ListTable/RowAction';
import ListTableApi from '@givewp/components/ListTable/api';
import {useContext} from 'react';
import {ShowConfirmModalContext} from '@givewp/components/ListTable/ListTablePage';
import styles from './DonorsRowActions.module.scss';
import {Interweave} from 'interweave';
import './style.scss';

const donorsApi = new ListTableApi(window.GiveDonors);

export function DonorsRowActions({item, setUpdateErrors, parameters}) {
    const showConfirmModal = useContext(ShowConfirmModalContext);
    const {mutate} = useSWRConfig();

    const fetchAndUpdateErrors = async (parameters, endpoint, id, method) => {
        const deleteDonations = document.querySelector('#giveDonorsTableDeleteDonations') as HTMLInputElement;
        const response = await donorsApi.fetchWithArgs(
            endpoint,
            {ids: [id], deleteDonationsAndRecords: deleteDonations.checked},
            method
        );
        setUpdateErrors(response);
        await mutate(parameters);
        return response;
    };

    const deleteDonor = async (selected) => await fetchAndUpdateErrors(parameters, '/delete', item.id, 'DELETE');

    const confirmDeleteDonor = (selected) => (
        <div>
            <p>{__('Really delete the follow donor?', 'give')}</p>
            <Interweave attributes={{className: 'donorBulkModalContent'}} content={item?.donorInformation} />
            <br></br>
            <input id="giveDonorsTableDeleteDonations" type="checkbox" defaultChecked={true} />
            <label htmlFor="giveDonorsTableDeleteDonations">
                {__('Delete all associated donations and records', 'give')}
            </label>
        </div>
    );

    const confirmModal = (event) => {
        showConfirmModal(__('Delete', 'give'), confirmDeleteDonor, deleteDonor, 'danger');
    };

    return (
        <div className={styles.container}>
            <RowAction
                className={styles.action}
                href={`/wp-admin/edit.php?post_type=give_forms&page=give-donors&view=overview&id=${item.id}`}
                displayText={__('Edit', 'give')}
            />
            <RowAction
                className={styles.action}
                onClick={confirmModal}
                actionId={item.id}
                displayText={__('Delete', 'give')}
                hiddenText={item.name}
                highlight
            />
        </div>
    );
}
