import {__} from "@wordpress/i18n";
import {ListTablePage} from "@givewp/components";
import {donorsColumns} from "./DonorsColumns";
import {DonorsRowActions} from "./DonorsRowActions";
import styles from "@givewp/components/ListTable/ListTablePage.module.scss";

declare global {
    interface Window {
        GiveDonors: {apiNonce: string; apiRoot: string};
    }
}

const donorsFilters = [
    {
        name: 'search',
        type: 'search',
        text: __('Name, Email, or Donor ID', 'give'),
        ariaLabel: __('Search donors', 'give')
    },
]

export default function DonorsListTable(){
    return (
        <ListTablePage
            title={__('Donors', 'give')}
            singleName={__('donors', 'give')}
            pluralName={__('donors', 'give')}
            columns={donorsColumns}
            {null/*rowActions={DonorsRowActions}*/}
            apiSettings={window.GiveDonors}
            filterSettings={donorsFilters}
        />
    );
}
