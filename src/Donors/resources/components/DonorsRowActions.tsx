import {__, sprintf} from "@wordpress/i18n";
import {useSWRConfig} from "swr";
import RowAction from "@givewp/components/ListTable/RowAction";
import ListTableApi from "@givewp/components/ListTable/api";
import {useContext} from "react";
import {ShowConfirmModalContext} from "@givewp/components/ListTable";

const donorsApi = new ListTableApi(window.GiveDonors);

export function DonorsRowActions({item, setUpdateErrors, parameters}) {
    const showConfirmModal = useContext(ShowConfirmModalContext);
    const {mutate} = useSWRConfig();

    const fetchAndUpdateErrors = async (parameters, endpoint, id, method) => {
        const deleteMeta = document.querySelector('#giveDonorsTableDeleteMeta') as HTMLInputElement;
        const response = await donorsApi.fetchWithArgs(endpoint, {ids: [id]}, method);
        setUpdateErrors(response);
        await mutate(parameters);
        return response;
    }

    const deleteDonor = async (selected) => await fetchAndUpdateErrors(parameters, '/delete', item.id, 'DELETE');

    const confirmDeleteDonor = (selected) => (
        <div>
            <p>
                {sprintf(__('Really delete donor record for %s?', 'give'), item.name)}
            </p>
            <input id='giveDonorsTableDeleteMeta' type='checkbox'/>
            <label htmlFor='giveDonorsTableDeleteMeta'>{__('Delete all associated donations and records', 'give')}</label>
        </div>
    );

    const confirmModal = (event) => {
        showConfirmModal(__('Delete', 'give'), confirmDeleteDonor, deleteDonor);
    }

    return (
        <>
            <RowAction
                href={`/wp-admin/edit.php?post_type=give_forms&page=give-donors&view=overview&id=${item.id}`}
                displayText={__('View Donor', 'give')}
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
}
