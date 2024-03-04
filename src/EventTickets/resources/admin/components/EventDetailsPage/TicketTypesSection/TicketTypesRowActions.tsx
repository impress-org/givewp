import {useContext} from 'react';
import {ShowConfirmModalContext} from './index';
import {__, sprintf} from '@wordpress/i18n';
import RowAction from '@givewp/components/ListTable/RowAction';
import {useSWRConfig} from 'swr';
import ListTableApi from '@givewp/components/ListTable/api';
import {ApiSettingsProps} from '../types';

const apiSettings: ApiSettingsProps = {
    ...window.GiveEventTicketsDetails,
    table: window.GiveEventTicketsDetails.ticketTypesTable,
};
apiSettings.apiRoot = apiSettings.apiRoot.replace('/list-table', '');
const eventTicketsApi = new ListTableApi(apiSettings);

export function TicketTypesRowActions({item, setUpdateErrors, parameters}) {
    const showConfirmModal = useContext(ShowConfirmModalContext);
    const {mutate} = useSWRConfig();

    console.log(item);

    const fetchAndUpdateErrors = async (parameters, endpoint, id, method) => {
        const response = await eventTicketsApi.fetchWithArgs(endpoint, {ids: [id]}, method);
        setUpdateErrors(response);
        await mutate(parameters);
        return response;
    };

    // Todo: Set correct endpoint
    const deleteItem = async (selected) => await fetchAndUpdateErrors(parameters, '', item.id, 'DELETE');

    const confirmDelete = (selected) => <p>{sprintf(__('Really delete ticket #%d?', 'give'), item.id)}</p>;

    const confirmModal = (event) => {
        showConfirmModal(__('Delete', 'give'), confirmDelete, deleteItem, 'danger');
    };

    return (
        <>
            {/*Todo: Add the edit modal*/}
            <RowAction
                href={`edit.php?post_type=give_forms&page=give-event-tickets&id=${item.id}`}
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
}
