import {ChangeEventHandler, createContext, useRef, useState} from "react";
import {__} from "@wordpress/i18n";

import {GiveIcon} from '@givewp/components';

import {ListTable, ListTableColumn} from './ListTable';
import Pagination from "./Pagination";
import {Filter, getInitialFilterState} from './Filters';
import useDebounce from "./hooks/useDebounce";
import {useResetPage} from "./hooks/useResetPage";
import ListTableApi from "./api";
import styles from './ListTablePage.module.scss';

export interface ListTablePageProps {
    //required
    title: string;
    columns: Array<ListTableColumn>;
    apiSettings: {apiRoot, apiNonce};

    //optional
    pluralName?: string;
    singleName?: string;
    children?: JSX.Element|JSX.Element[]|null;
    rowActions?: JSX.Element|JSX.Element[]|Function|null;
    filterSettings?;
}

export const RowActionsContext = createContext({});

export default function ListTablePage({
    title,
    columns,
    apiSettings,
    filterSettings = [],
    singleName = __('item', 'give'),
    pluralName  = __('items', 'give'),
    rowActions = null,
    children = null,
}: ListTablePageProps) {
    const [page, setPage] = useState<number>(1);
    const [perPage, setPerPage] = useState<number>(10);
    const [filters, setFilters] = useState(getInitialFilterState(filterSettings));

    const parameters = {
        page,
        perPage,
        ...filters
    };

    const archiveApi = useRef(new ListTableApi(apiSettings)).current;

    const {data, error, isValidating} = archiveApi.useListTable(parameters)

    useResetPage(data, page, setPage, filters);

    const handleDebouncedFilterChange = useDebounce((name, value) =>
        setFilters(prevState => ({...prevState, [name]: value}))
    );

    const handleFilterChange = (name, value) => {
        setFilters(prevState => ({...prevState, [name]: value}));
    }

    const showPagination = () => (
        <Pagination
            currentPage={page}
            totalPages={data ? data.totalPages : 1}
            disabled={!data}
            totalItems={data ? parseInt(data.totalItems) : -1}
            setPage={setPage}
        />
    )

    return (
        <article>
            <div className={styles.pageHeader}>
                <GiveIcon size={'1.875rem'}/>
                <h1 className={styles.pageTitle}>{title}</h1>
                {children}
            </div>
            <div className={styles.searchContainer}>
                {filterSettings.map(filter =>
                    <Filter key={filter.name} filter={filter} onChange={handleFilterChange} debouncedOnChange={handleDebouncedFilterChange}/>
                )}
            </div>
            <div className={styles.pageContent}>
                <div className={styles.pageActions}>
                    {page && setPage && showPagination()}
                </div>
                <ListTable
                    columns={columns}
                    singleName={singleName}
                    pluralName={pluralName}
                    title={title}
                    rowActions={rowActions}
                    parameters={parameters}
                    data={data}
                    error={error}
                    isLoading={isValidating}
                />
                <div className={styles.pageActions}>
                    {page && setPage && showPagination()}
                </div>
            </div>
        </article>
    );
}
