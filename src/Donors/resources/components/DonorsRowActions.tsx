import {__} from '@wordpress/i18n';
import {useSWRConfig} from 'swr';
import RowAction from '@givewp/components/ListTable/RowAction';
import ListTableApi from '@givewp/components/ListTable/api';
import {useContext} from 'react';
import {ShowConfirmModalContext} from '@givewp/components/ListTable/ListTablePage';
import {Interweave} from 'interweave';
import './style.scss';

const donorsApi = new ListTableApi(window.GiveDonors);

export function DonorsRowActions({item, setUpdateErrors, parameters}) {
    const showConfirmModal = useContext(ShowConfirmModalContext);
    const {mutate} = useSWRConfig();

    const deleteDonors = async (id: number) => {
        const deleteDonations = document.querySelector('#giveDonorsTableDeleteDonations') as HTMLInputElement;
        const response = await donorsApi.fetchWithArgs(
            '/delete',
            {
                ids: [id],
                deleteDonationsAndRecords: deleteDonations.checked
            },
            'DELETE'
        );
        setUpdateErrors(response);
        await mutate(parameters);
        return response;
    };

    const updateDonorStatus = async (id: number, status: string) => {
        const response = await donorsApi.fetchWithArgs(
            '/status',
            {
                ids: [id],
                status
            },
            'POST'
        );
        setUpdateErrors(response);
        await mutate(parameters);
        return response;
    };

    const deleteDonor = async () => await deleteDonors( item.id);
    const trashDonor = async () => await updateDonorStatus( item.id, 'trash');
    const restoreDonor = async () => await updateDonorStatus( item.id, 'active');

    const confirmDeleteDonor = () => (
        <div>
            <p>{__('Permamently delete the follow donor?', 'give')}</p>
            <Interweave attributes={{className: 'donorBulkModalContent'}} content={item?.donorInformation} />
            <br></br>
            <input id="giveDonorsTableDeleteDonations" type="checkbox" defaultChecked={true} />
            <label htmlFor="giveDonorsTableDeleteDonations">
                {__('Delete all associated donations and records', 'give')}
            </label>
        </div>
    );

    const confirmTrashDonor = () => (
        <div>
            <p>{__('Trash the following donor?', 'give')}</p>
            <Interweave attributes={{className: 'donorBulkModalContent'}} content={item?.donorInformation} />
        </div>
    );

    const confirmRestoreDonor = () => (
        <div>
            <p>{__('Restore the following donor?', 'give')}</p>
            <Interweave attributes={{className: 'donorBulkModalContent'}} content={item?.donorInformation} />
        </div>
    );

    const confirmDeleteModal = () => {
        showConfirmModal(__('Delete', 'give'), confirmDeleteDonor, deleteDonor, 'danger');
    };

    const confirmTrashModal = () => {
        showConfirmModal(__('Trash', 'give'), confirmTrashDonor, trashDonor, 'warning');
    };

    const confirmRestoreModal = () => {
        showConfirmModal(__('Restore', 'give'), confirmRestoreDonor, restoreDonor, 'normal');
    };

    return (
        <>
            <RowAction
                href={
                    window.GiveDonors.adminUrl +
                    `edit.php?post_type=give_forms&page=give-donors&view=overview&id=${item.id}`
                }
                displayText={__('Edit', 'give')}
            />
            {parameters.status === 'trash' ? (
                <>
                    <RowAction
                        onClick={confirmRestoreModal}
                        actionId={item.id}
                        displayText={__('Restore', 'give')}
                    />
                    <RowAction
                        onClick={confirmDeleteModal}
                        actionId={item.id}
                        displayText={__('Delete', 'give')}
                        hiddenText={item.name}
                        highlight
                    />
                </>
            ) : (
                <RowAction
                    onClick={confirmTrashModal}
                    actionId={item.id}
                    displayText={__('Trash', 'give')}
                    hiddenText={item.name}
                    highlight
                />
            )}
        </>
    );
}
