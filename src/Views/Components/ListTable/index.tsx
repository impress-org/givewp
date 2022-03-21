import {useEffect, useRef, useState} from 'react';
import type {ChangeEventHandler} from 'react';

import styles from './ListTablePage.module.scss';
import {ListTable, ListTableColumn} from './ListTable';
import {GiveIcon} from '@givewp/components';
import {debounce} from 'lodash';
import ListTableApi from './api';
import Pagination from "./Pagination";

const donationFormsApi = new ListTableApi(window.GiveDonationForms);

interface SearchFilterProps {
    name: string;
    type: string;
    text: string;
    ariaLabel?: string;
}

interface SelectFilterProps extends SearchFilterProps {
    values: any;
    options: Array<{name: string, text: string}>;
}

export interface ListTablePageProps {
    headerButtons: Array<{text: string, link: string}>;
    filters: Array<SearchFilterProps|SelectFilterProps>;
    singleName: string;
    pluralName: string;
    title: string;
    columns: Array<ListTableColumn>;
}

export default function ListTablePage({
    headerButtons = [],
    filters = [],
    singleName,
    pluralName,
    title,
    columns
}: ListTablePageProps) {
    const [page, setPage] = useState<number>(1);
    const [perPage, setPerPage] = useState<number>(10);
    const [pageFilters, setFilters] = useState(getInitialFilterState(filters));

    useEffect(() => {
        setPage(1);
    }, [filters]);

    const setFiltersLater = useRef(
        debounce((name, value) =>
            setFilters(prevState => ({...prevState, [name]: value})),
            500
        )
    ).current;

    const {data, error, isValidating} = donationFormsApi.useListForms({page, perPage, ...pageFilters})

    useEffect(() => {
        return () => {
            setFiltersLater.cancel();
        }
    }, []);

    const handleFilterChange: ChangeEventHandler<HTMLInputElement|HTMLSelectElement> = (event) => {
        event.persist();
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
                {headerButtons.map(button => (
                    <a key={button.link} href={button.link} className={styles.addFormButton}>
                        {button.text}
                    </a>
                ))}
            </div>
            <div className={styles.searchContainer}>
                {filters.map((filter) => (
                    <TableFilter
                        key={filter.name}
                        filter={filter}
                        onChange={handleFilterChange}
                        debouncedOnChange={handleDebouncedFilterChange}
                    />
                ))}
            </div>
            <div className={styles.pageContent}>
                <div className={styles.pageActions}>
                    <Pagination
                        currentPage={page}
                        totalPages={data ? data.totalPages : 1}
                        disabled={!data}
                        totalItems={data ? parseInt(data.totalItems) : -1}
                        setPage={setPage}
                    />
                </div>
                    <ListTable
                        filters={{...pageFilters}}
                        columns={columns}
                        singleName={singleName}
                        pluralName={pluralName}
                        title={title}
                        data={data}
                        error={error}
                        isValidating={isValidating}
                        parameters={{page, perPage, ...pageFilters}}
                    />
                <div className={styles.pageActions}>
                    <Pagination
                        currentPage={page}
                        totalPages={data ? data.totalPages : 1}
                        disabled={!data}
                        totalItems={data ? parseInt(data.totalItems) : -1}
                        setPage={setPage}
                    />
                </div>
            </div>
        </article>
    );
}

const TableFilter = ({ filter, onChange, debouncedOnChange }) => {
        switch(filter.type){
            case 'select':
                return (
                    <select
                        name={filter.name}
                        className={styles.statusFilter}
                        aria-label={filter?.ariaLabel}
                        onChange={onChange}
                    >
                        {filter.options.map(({name, text}) => (
                            <option key={name} value={name}>
                                {text}
                            </option>
                        ))}
                    </select>
                );
            case 'search':
                return (
                    <input
                        type="search"
                        aria-label={filter?.ariaLabel}
                        placeholder={filter?.text}
                        onChange={debouncedOnChange}
                        className={styles.searchInput}
                    />
                );
            default:
                break;
        }
}

const getInitialFilterState = (filters) => {
    const state = {};
    filters.map((filter) => {
        switch (filter.type) {
            case 'select':
                state[filter.name] = filter.options?.[0].name
                break;
            case 'search':
            default:
                state[filter.name] = '';
                break;
        }
    });
    return state;
}
