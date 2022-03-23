import RowAction from "../../../Views/Components/ListTable/RowAction";
import {__} from "@wordpress/i18n";
import ListTableApi from "../../../Views/Components/ListTable/api";
import {RowActionsContext} from "./DonationFormsListTable";
import {useSWRConfig} from "swr";

const donationFormsApi = new ListTableApi(window.GiveDonationForms);

const fetchAndUpdateErrors = async (mutate, parameters, endpoint, setUpdateErrors, id, method) => {
    const response = await donationFormsApi.fetchWithArgs(endpoint, {ids: [id]}, method);
    setUpdateErrors(response);
    await mutate(parameters);
    return response;
}

const deleteForm = async (mutate, parameters, setUpdateErrors, id, trashEnabled = false) => {
    const endpoint = trashEnabled ? '/trash' : '/delete';
    await fetchAndUpdateErrors(mutate, parameters, endpoint, setUpdateErrors, id, 'DELETE');
}

const restoreForm = async (mutate, parameters, setUpdateErrors, id) => {
    await fetchAndUpdateErrors(mutate, parameters,'/restore', setUpdateErrors, id, 'POST');
}

const duplicateForm = async(mutate, parameters, setUpdateErrors, id) => {
    return await fetchAndUpdateErrors(mutate, parameters,'/duplicate', setUpdateErrors, id, 'POST');
}

export function DonationFormsRowActions ({data, item, removeRow, addRow, setUpdateErrors}){
    const trashEnabled = Boolean(data?.trash);
    const {mutate} = useSWRConfig();
    return (
        <RowActionsContext.Consumer>
            {(parameters) => parameters.status === 'trash' ? (
                <>
                    <RowAction
                        onClick={removeRow(async () => await restoreForm(mutate, parameters, setUpdateErrors, item.id))}
                        actionId={item.id}
                        displayText={__('Restore', 'give')}
                        hiddenText={item.name}
                    />
                    <RowAction
                        onClick={removeRow(async () => await deleteForm(mutate, parameters, setUpdateErrors, item.id))}
                        actionId={item.id}
                        displayText={__('Delete Permanently', 'give')}
                        hiddenText={item.name}
                        highlight
                    />
                </>
            ) : (
                <>
                    <RowAction
                        href={item.edit}
                        displayText={__('Edit', 'give')}
                        hiddenText={item.name}
                    />
                    <RowAction
                        onClick={removeRow(async () => await deleteForm(mutate, parameters, setUpdateErrors, item.id))}
                        actionId={item.id}
                        highlight={!trashEnabled}
                        displayText={trashEnabled ? __('Trash', 'give') : __('Delete', 'give')}
                        hiddenText={item.name}
                    />
                    <RowAction
                        href={item.permalink}
                        displayText={__('View', 'give')}
                        hiddenText={item.name}
                    />
                    <RowAction
                        onClick={addRow(async (id) => await duplicateForm(mutate, parameters, setUpdateErrors, id))}
                        actionId={item.id}
                        displayText={__('Duplicate', 'give')}
                        hiddenText={item.name}
                    />
                </>
            )}
        </RowActionsContext.Consumer>
    );
}
