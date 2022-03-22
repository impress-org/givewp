import {ListTablePage} from "@givewp/components";
import {__} from "@wordpress/i18n";
import {donationFormsColumns} from "./DonationFormsColumns";
import {ChangeEventHandler, useEffect, useState} from "react";
import useDebounce from "../../../Views/Components/ListTable/hooks/useDebounce";
import ListTableApi from "../../../Views/Components/ListTable/api";
import styles from "../../../Views/Components/ListTable/ListTablePage.module.scss";

declare global {
    interface Window {
        GiveDonationForms: {apiNonce: string; apiRoot: string};
    }
}

const donationFormsApi = new ListTableApi(window.GiveDonationForms);

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

const headerButtons = [
    {
        text: __('Add Form', 'give'),
        link: 'post-new.php?post_type=give_forms',
    }
];

export default function DonationFormsListTable(){
    const [page, setPage] = useState<number>(1);
    const [perPage, setPerPage] = useState<number>(10);
    const [filters, setFilters] = useState({search: ''});

    const setFiltersLater = useDebounce((name, value) =>
        setFilters(prevState => ({...prevState, [name]: value}))
    );
    useEffect(() => {
        setPage(1);
    }, [filters]);

    const {data, error, isValidating} = donationFormsApi.useListTable({page, perPage, ...filters})

    //if we're displaying a non-existent page (like after deleting an item), go to the last available page
    useEffect(() => {
        if(data?.totalPages && page > data.totalPages){
            setPage(data.totalPages);
        }
    }, [data]);

    const handleFilterChange: ChangeEventHandler<HTMLInputElement|HTMLSelectElement> = (event) => {
        setFilters(prevState => ({...prevState, [event.target.name]: event.target.value}));
    }

    const handleDebouncedFilterChange: ChangeEventHandler<HTMLInputElement|HTMLSelectElement> = (event) => {
        event.persist();
        setFiltersLater(event.target.name, event.target.value);
    }

    return (
        <ListTablePage
            title={__('Donation Forms', 'give')}
            singleName={__('donation form', 'give')}
            pluralName={__('donation forms', 'give')}
            headerButtons={headerButtons}
            columns={donationFormsColumns}
            data={data}
            error={error}
            isValidating={isValidating}
            page={page}
            setPage={setPage}
        >
            <input
                type='search'
                name='search'
                aria-label={__('Search donation forms', 'give')}
                placeholder={__('Search by name or ID', 'give')}
                onChange={handleDebouncedFilterChange}
                className={styles.searchInput}
            />
            <select
                name='status'
                className={styles.statusFilter}
                aria-label={__('Filter donation forms by status', 'give')}
                onChange={handleFilterChange}
            >
                {donationStatus.map(({name, text}) => (
                    <option key={name} value={name}>
                        {text}
                    </option>
                ))}
            </select>
        </ListTablePage>
    );
}
