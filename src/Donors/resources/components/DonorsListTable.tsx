import {__} from "@wordpress/i18n";
import {ListTablePage} from "@givewp/components";
import {donorsColumns} from "./DonorsColumns";
import {DonorsRowActions} from "./DonorsRowActions";

declare global {
    interface Window {
        GiveDonors;
    }
}

const donorsFilters = [
    {
        name: 'search',
        type: 'search',
        text: __('Name, Email, or Donor ID', 'give'),
        ariaLabel: __('Search donors', 'give')
    },
    {
        name: 'form',
        type: 'searchableselect',
        text: __('Select Form', 'give'),
        ariaLabel: __('Filter donation forms by status', 'give'),
        options: window.GiveDonors.forms
    }
]

export default function DonorsListTable(){
    return (
        <ListTablePage
            title={__('Donors', 'give')}
            singleName={__('donors', 'give')}
            pluralName={__('donors', 'give')}
            columns={donorsColumns}
            rowActions={DonorsRowActions}
            apiSettings={window.GiveDonors}
            filterSettings={donorsFilters}
        />
    );
}
