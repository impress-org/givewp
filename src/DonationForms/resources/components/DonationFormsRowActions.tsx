import RowAction from "../../../Views/Components/ListTable/RowAction";
import {__} from "@wordpress/i18n";
import {useSWRConfig} from "swr";
import ListTableApi from "../../../Views/Components/ListTable/api";

const donationFormsApi = new ListTableApi(window.GiveDonationForms);

const deleteForm = (mutate, endpoint, id) => {
    donationFormsApi.fetchWithArgs(endpoint, {ids: [id]}, 'DELETE')
        .then((res) => mutate());
}

export const DonationFormsRowActions = ({data, item, removeRow, addRow}) => {
    const trashEnabled = Boolean(data?.trash);
    const {mutate} = useSWRConfig();
    if(false/*parameters.status === 'trash'*/) {
        return (
            <>
                <RowAction
                    onClick={removeRow(() => console.log('restored'))}
                    actionId={item.id}
                    displayText={__('Restore', 'give')}
                    hiddenText={item.name}
                />
                <RowAction
                    onClick={removeRow(() => console.log('removed'))}
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
                onClick={removeRow(() => deleteForm(mutate, trashEnabled ? '/trash' : '/delete', item.id))}
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
                onClick={addRow(()=> console.log('duplicated'))}
                actionId={item.id}
                displayText={__('Duplicate', 'give')}
                hiddenText={item.name}
            />
        </>
    );
}
