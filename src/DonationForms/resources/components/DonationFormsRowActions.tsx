import RowAction from "../../../Views/Components/ListTable/RowAction";
import {__} from "@wordpress/i18n";
import {useSWRConfig} from "swr";

/*async function mutateForm(ids, endpoint, method, remove = false) {
    try {
        const response = await api.fetchWithArgs(endpoint, {ids}, method);
        // if we just removed the last entry from the page and we're not on the first page, go back a page
        if (remove && !response.errors.length && data.items.length == 1 && data.totalPages > 1) {
            setPage(page - 1);
        }
        // otherwise, revalidate current page
        else {
            await mutate(listParams);
        }
        //revalidate all pages after the current page
        const mutations = [];
        for (let i = page + 1; i <= data.totalPages; i++) {
            mutations.push(mutate({...listParams, page: i}));
        }
        setErrors(response.errors);
        setSuccesses(response.successes);
        return response;
    } catch (error) {
        setErrors(ids.split(','));
        setSuccesses([]);
        return {errors: ids.split(','), successes: []};
    }
}*/

export const DonationFormsRowActions = ({data, item, parameters, removeRow, addRow}) => {
    const trashEnabled = Boolean(data?.trash);
    const {mutate} = useSWRConfig();
    if(parameters.status === 'trash') {
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
                onClick={removeRow(() => console.log(trashEnabled ? '/trash' : '/delete'))}
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
