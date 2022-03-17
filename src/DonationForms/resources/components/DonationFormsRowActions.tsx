import RowAction from "../../../Views/Components/ListTable/RowAction";
import {__} from "@wordpress/i18n";

export const DonationFormsRowActions = ({data, item, parameters, removeRow, addRow}) => {
    const trashEnabled = Boolean(data?.trash);

    if(parameters.status == 'trash') {
        return (
            <>
                <RowAction
                    onClick={removeRow('/restore', 'POST')}
                    actionId={item.id}
                    displayText={__('Restore', 'give')}
                    hiddenText={item.name}
                />
                <RowAction
                    onClick={removeRow('/delete', 'DELETE')}
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
                onClick={removeRow((trashEnabled ? '/trash' : '/delete'), 'DELETE')}
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
                onClick={addRow('/duplicate', 'POST')}
                actionId={item.id}
                displayText={__('Duplicate', 'give')}
                hiddenText={item.name}
            />
        </>
    );
}
