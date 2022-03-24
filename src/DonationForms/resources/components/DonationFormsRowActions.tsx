import {__} from "@wordpress/i18n";
import {useSWRConfig} from "swr";
import RowAction from "@givewp/components/ListTable/RowAction";
import ListTableApi from "@givewp/components/ListTable/api";

const donationFormsApi = new ListTableApi(window.GiveDonationForms);

export function DonationFormsRowActions({data, item, removeRow, addRow, setUpdateErrors, parameters}) {
    const {mutate} = useSWRConfig();
    const trashEnabled = Boolean(data?.trash);

    const fetchAndUpdateErrors = async (parameters, endpoint, id, method) => {
        const response = await donationFormsApi.fetchWithArgs(endpoint, {ids: [id]}, method);
        setUpdateErrors(response);
        await mutate(parameters);
        return response;
    }

    return (
        <>
            {parameters.status === 'trash' ? (
                <>
                    <RowAction
                        onClick={removeRow(async () => await fetchAndUpdateErrors(parameters, '/restore', item.id, 'POST'))}
                        actionId={item.id}
                        displayText={__('Restore', 'give')}
                        hiddenText={item.name}
                    />
                    <RowAction
                        onClick={removeRow(async () => await fetchAndUpdateErrors(parameters, '/delete', item.id, 'DELETE'))}
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
                        onClick={removeRow(async () => {
                            const endpoint = trashEnabled ? '/trash' : '/delete';
                            await fetchAndUpdateErrors(parameters, endpoint, item.id, 'DELETE');
                        })}
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
                        onClick={addRow(async (id) => await fetchAndUpdateErrors(parameters, '/duplicate', id, 'POST'))}
                        actionId={item.id}
                        displayText={__('Duplicate', 'give')}
                        hiddenText={item.name}
                    />
                </>
            )}
        </>
    );
}
