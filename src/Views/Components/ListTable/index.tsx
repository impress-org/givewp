import styles from './ListTablePage.module.scss';
import {ListTable, ListTableColumn} from './ListTable';
import {GiveIcon} from '@givewp/components';
import Pagination from "./Pagination";
import {__} from "@wordpress/i18n";
import {ChangeEventHandler, createContext, useRef, useState} from "react";
import useDebounce from "./hooks/useDebounce";
import {useResetPage} from "./hooks/useResetPage";
import ListTableApi from "./api";

export interface ListTablePageProps {
    //required
    title: string;
    columns: Array<ListTableColumn>;
    data: {items: Array<{}>, totalPages: number, totalItems: string};
    apiSettings: {apiRoot, apiNonce};

    //optional
    pluralName?: string;
    singleName?: string;
    inHeader?: JSX.Element|JSX.Element[]|null;
    children?: JSX.Element|JSX.Element[]|null;
    rowActions: JSX.Element|JSX.Element[]|null;
    page?: number;
    setPage?: null|((page: number) => void);
}

export const RowActionsContext = createContext({});

export default function ListTablePage({
    title,
    columns,
    apiSettings,
    singleName = __('item', 'give'),
    pluralName  = __('items', 'give'),
    rowActions = null,
    inHeader = null,
}: ListTablePageProps) {
    const [page, setPage] = useState<number>(1);
    const [perPage, setPerPage] = useState<number>(10);
    const [filters, setFilters] = useState({search: '', status: 'any'});

    const setFiltersLater = useDebounce((name, value) =>
        setFilters(prevState => ({...prevState, [name]: value}))
    );

    const parameters = {
        page,
        perPage,
        ...filters
    };

    const archiveApi = useRef(new ListTableApi(apiSettings)).current;

    const {data, error, isValidating} = archiveApi.useListTable(parameters)

    useResetPage(data, page, setPage, filters);

    const handleFilterChange: ChangeEventHandler<HTMLInputElement|HTMLSelectElement> = (event) => {
        setFilters(prevState => ({...prevState, [event.target.name]: event.target.value}));
    }

    const handleDebouncedFilterChange: ChangeEventHandler<HTMLInputElement|HTMLSelectElement> = (event) => {
        event.persist();
        setFiltersLater(event.target.name, event.target.value);
    }

    return (
        <article>
            <div className={styles.pageHeader}>
                <GiveIcon size={'1.875rem'}/>
                <h1 className={styles.pageTitle}>{title}</h1>
                {inHeader}
            </div>
            <div className={styles.searchContainer}>
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
                    {[].map(({name, text}) => (
                        <option key={name} value={name}>
                            {text}
                        </option>
                    ))}
                </select>
            </div>
            <div className={styles.pageContent}>
                <div className={styles.pageActions}>
                    {page && setPage &&
                        <Pagination
                            currentPage={page}
                            totalPages={data ? data.totalPages : 1}
                            disabled={!data}
                            totalItems={data ? parseInt(data.totalItems) : -1}
                            setPage={setPage}
                        />
                    }
                </div>
                    <RowActionsContext.Provider value={parameters}>
                        <ListTable
                            columns={columns}
                            singleName={singleName}
                            pluralName={pluralName}
                            title={title}
                            rowActions={rowActions}
                            data={data}
                            error={error}
                            isLoading={isValidating}
                        />
                    </RowActionsContext.Provider>
                <div className={styles.pageActions}>
                    {page && setPage &&
                        <Pagination
                            currentPage={page}
                            totalPages={data ? data.totalPages : 1}
                            disabled={!data}
                            totalItems={data ? parseInt(data.totalItems) : -1}
                            setPage={setPage}
                        />
                    }
                </div>
            </div>
        </article>
    );
}
