import {ListTablePage} from "@givewp/components";
import {__} from "@wordpress/i18n";
import {donationFormsColumns} from "./DonationFormsColumns";
import {ChangeEventHandler, createContext, useEffect, useState} from "react";
import useDebounce from "../../../Views/Components/ListTable/hooks/useDebounce";
import ListTableApi from "../../../Views/Components/ListTable/api";
import styles from "../../../Views/Components/ListTable/ListTablePage.module.scss";
import {useResetPage} from "../../../Views/Components/ListTable/hooks/useResetPage";
import {DonationFormsRowActions} from "./DonationFormsRowActions";
import {useSWRConfig} from "swr";

declare global {
    interface Window {
        GiveDonationForms: {apiNonce: string; apiRoot: string};
    }
}

const donationStatus = [
    {
        name: 'any',
        text: __('All', 'give'),
    },
    {
        name: 'publish',
        text: __('Published', 'give'),
    },
    {
        name: 'pending',
        text: __('Pending', 'give'),
    },
    {
        name: 'draft',
        text: __('Draft', 'give'),
    },
    {
        name: 'trash',
        text: __('Trash', 'give'),
    }
]

const headerButtons = (
    <a href={'post-new.php?post_type=give_forms'} className={styles.addFormButton}>
        {__('Add Form', 'give')}
    </a>
);

export default function DonationFormsListTable(){


    return (
        <ListTablePage
            title={__('Donation Forms', 'give')}
            singleName={__('donation form', 'give')}
            pluralName={__('donation forms', 'give')}
            inHeader={headerButtons}
            columns={donationFormsColumns}
            rowActions={DonationFormsRowActions}
            apiSettings={window.GiveDonationForms}
        >

        </ListTablePage>
    );
}
