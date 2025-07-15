import {useContext} from 'react';
import {ShowConfirmModalContext} from '@givewp/components/ListTable/ListTablePage';
import {__, sprintf} from '@wordpress/i18n';
import RowAction from '@givewp/components/ListTable/RowAction';
import {useSWRConfig} from 'swr';
import { useDonationDelete } from '@givewp/donations/hooks/useDonationDelete';


export const DonationRowActions = ({item, removeRow, setUpdateErrors, parameters}) => {
    const showConfirmModal = useContext(ShowConfirmModalContext);
    const {deleteDonation} = useDonationDelete();


    const deleteItem = async (selected) => {
        await deleteDonation(item.id);
        window.location.reload();
    };

    const confirmDelete = (selected) => <p>{sprintf(__('Are you sure you want to move donation #%d to the trash? You can restore it later if needed.', 'give'), item.id)}</p>;

    const confirmModal = (event) => {
        showConfirmModal(__('Move donation to trash', 'give'), confirmDelete, deleteItem, 'danger', __('Trash Donation', 'give'));
    };

    return (
        <>
            <RowAction
                href={
                    window.GiveDonations.adminUrl +
                    `edit.php?post_type=give_forms&page=give-payment-history&id=${item.id}`
                }
                displayText={__('Edit', 'give')}
            />
            <RowAction
                onClick={confirmModal}
                actionId={item.id}
                displayText={__('Delete', 'give')}
                hiddenText={item.name}
                highlight
            />
        </>
    );
};
