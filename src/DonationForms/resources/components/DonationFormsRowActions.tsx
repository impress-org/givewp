import RowAction from "../../../Views/Components/ListTable/RowAction";
import {__} from "@wordpress/i18n";
import ListTableApi from "../../../Views/Components/ListTable/api";

const donationFormsApi = new ListTableApi(window.GiveDonationForms);

const fetchAndUpdateErrors = async (mutate, endpoint, setUpdateErrors, id, method) => {
    const response = await donationFormsApi.fetchWithArgs(endpoint, {ids: [id]}, method);
    setUpdateErrors(response);
    mutate();
}

const deleteForm = async (mutate, setUpdateErrors, id, trashEnabled = false) => {
    const endpoint = trashEnabled ? '/trash' : '/delete';
    await fetchAndUpdateErrors(mutate, endpoint, setUpdateErrors, id, 'DELETE');
}

const restoreForm = async (mutate, setUpdateErrors, id) => {
    await fetchAndUpdateErrors(mutate,'/restore', setUpdateErrors, id, 'POST');
}

const duplicateForm = async(mutate, setUpdateErrors, id) => {
    await fetchAndUpdateErrors(mutate,'/duplicate', setUpdateErrors, id, 'POST');
}

export function DonationFormsRowActions ({data, item, removeRow, addRow, setUpdateErrors}){
    const trashEnabled = Boolean(data?.trash);
    if(this.parameters.status === 'trash') {
        return (
            <>
                <RowAction
                    onClick={removeRow(async () => await restoreForm(this.mutate, setUpdateErrors, item.id))}
                    actionId={item.id}
                    displayText={__('Restore', 'give')}
                    hiddenText={item.name}
                />
                <RowAction
                    onClick={removeRow(async () => await deleteForm(this.mutate, setUpdateErrors, item.id))}
                    actionId={item.id}
                    displayText={__('Delete Permanently', 'give')}
                    hiddenText={item.name}
                    highlight
                />
            </>
        );
    }

    return (
        <>
            <RowAction
                href={item.edit}
                displayText={__('Edit', 'give')}
                hiddenText={item.name}
            />
            <RowAction
                onClick={removeRow(async () => await deleteForm(this.mutate, setUpdateErrors, item.id))}
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
                onClick={addRow(() => duplicateForm(this.mutate, setUpdateErrors, item.id))}
                actionId={item.id}
                displayText={__('Duplicate', 'give')}
                hiddenText={item.name}
            />
        </>
    );
}
