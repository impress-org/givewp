import {__} from "@wordpress/i18n";
import {ListTablePage} from "@givewp/components";
import {donorsColumns} from "./DonorsColumns";
import {DonorsRowActions} from "./DonorsRowActions";

declare global {
    interface Window {
        GiveDonors;
    }
}

const donationStatus = [
    {
        value: 'any',
        text: __('All', 'give'),
    },
    {
        value: 'publish',
        text: __('Published', 'give'),
    },
    {
        value: 'pending',
        text: __('Pending', 'give'),
    },
    {
        value: 'draft',
        text: __('Draft', 'give'),
    },
    {
        value: 'trash',
        text: __('Trash', 'give'),
    }
]

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
        options: donationStatus
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
