import RowAction from "../../../Views/Components/ListTable/RowAction";
import {__} from "@wordpress/i18n";
import ListTableApi from "../../../Views/Components/ListTable/api";
import {RowActionsContext} from "./DonationFormsListTable";

const donationFormsApi = new ListTableApi(window.GiveDonationForms);

const fetchAndUpdateErrors = async (mutate, endpoint, setUpdateErrors, id, method) => {
    const response = await donationFormsApi.fetchWithArgs(endpoint, {ids: [id]}, method);
    setUpdateErrors(response);
    await mutate();
    return response;
}

const deleteForm = async (mutate, setUpdateErrors, id, trashEnabled = false) => {
    const endpoint = trashEnabled ? '/trash' : '/delete';
    await fetchAndUpdateErrors(mutate, endpoint, setUpdateErrors, id, 'DELETE');
}

const restoreForm = async (mutate, setUpdateErrors, id) => {
    await fetchAndUpdateErrors(mutate,'/restore', setUpdateErrors, id, 'POST');
}

const duplicateForm = async(mutate, setUpdateErrors, id) => {
    return await fetchAndUpdateErrors(mutate,'/duplicate', setUpdateErrors, id, 'POST');
}

export function DonationFormsRowActions ({data, item, removeRow, addRow, setUpdateErrors}){
    const trashEnabled = Boolean(data?.trash);
    return (
        <RowActionsContext.Consumer>
            {(context) => context.parameters.status === 'trash' ? (
                <>
                    <RowAction
                        onClick={removeRow(async () => await restoreForm(context.mutate, setUpdateErrors, item.id))}
                        actionId={item.id}
                        displayText={__('Restore', 'give')}
                        hiddenText={item.name}
                    />
                    <RowAction
                        onClick={removeRow(async () => await deleteForm(context.mutate, setUpdateErrors, item.id))}
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
                        onClick={removeRow(async () => await deleteForm(context.mutate, setUpdateErrors, item.id))}
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
                        onClick={addRow(async (id) => await duplicateForm(context.mutate, setUpdateErrors, id))}
                        actionId={item.id}
                        displayText={__('Duplicate', 'give')}
                        hiddenText={item.name}
                    />
                </>
            )}
        </RowActionsContext.Consumer>
    );
}
