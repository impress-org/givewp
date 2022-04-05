import {__} from "@wordpress/i18n";
import {useSWRConfig} from "swr";
import RowAction from "@givewp/components/ListTable/RowAction";
import ListTableApi from "@givewp/components/ListTable/api";

const donorsApi = new ListTableApi(window.GiveDonors);

export function DonorsRowActions({data, item, removeRow, addRow, setUpdateErrors, parameters}) {
    const {mutate} = useSWRConfig();

    const fetchAndUpdateErrors = async (parameters, endpoint, id, method) => {
        const response = await donorsApi.fetchWithArgs(endpoint, {ids: [id]}, method);
        setUpdateErrors(response);
        await mutate(parameters);
        return response;
    }

    return (
        <>
            <RowAction
                href={`/wp-admin/edit.php?post_type=give_forms&page=give-donors&view=overview&id=${item.id}`}
                displayText={__('Edit', 'give')}
            />
            <RowAction
                onClick={removeRow(async () => await fetchAndUpdateErrors(parameters, '/delete', item.id, 'DELETE'))}
                actionId={item.id}
                displayText={__('Delete', 'give')}
                hiddenText={item.name}
                highlight
            />
        </>
    );
}
