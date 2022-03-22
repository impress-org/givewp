import RowAction from "../../../Views/Components/ListTable/RowAction";
import {__} from "@wordpress/i18n";
import ListTableApi from "../../../Views/Components/ListTable/api";

const donationFormsApi = new ListTableApi(window.GiveDonationForms);

const deleteForm = (mutate, endpoint, id) => {
    donationFormsApi.fetchWithArgs(endpoint, {ids: [id]}, 'DELETE')
        .then((res) => mutate());
}

const restoreForm = (mutate, id) => {
    donationFormsApi.fetchWithArgs('/restore', {ids: [id]}, 'POST')
        .then((res) => mutate());
}

const duplicateForm = (mutate, id) => {
    donationFormsApi.fetchWithArgs('/duplicate', {ids: [id]}, 'POST')
        .then((res) => mutate());
}

export function DonationFormsRowActions ({data, item, removeRow, addRow}){
    const trashEnabled = Boolean(data?.trash);
    if(this.parameters.status === 'trash') {
        return (
            <>
                <RowAction
                    onClick={removeRow(() => restoreForm(this.mutate, item.id))}
                    actionId={item.id}
                    displayText={__('Restore', 'give')}
                    hiddenText={item.name}
                />
                <RowAction
                    onClick={removeRow(() => deleteForm(this.mutate, '/delete', item.id))}
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
                onClick={removeRow(() => deleteForm(this.mutate, trashEnabled ? '/trash' : '/delete', item.id))}
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
                onClick={addRow(() => duplicateForm(this.mutate, item.id))}
                actionId={item.id}
                displayText={__('Duplicate', 'give')}
                hiddenText={item.name}
            />
        </>
    );
}
