import {useEffect, useRef, useState} from 'react';
import type {ChangeEventHandler} from 'react';

import styles from './ListTablePage.module.scss';
import {ListTable, ListTableColumn} from '@givewp/components';
import {GiveIcon} from '@givewp/components';
import {debounce} from 'lodash';

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

export interface ListFormsPageProps {
    headerButtons: Array<{text: string, link: string}>;
    filters: Array<SearchFilterProps|SelectFilterProps>;
    singleName: string;
    pluralName: string;
    title: string;
    columns: Array<ListTableColumn>;
    api: any;
}

export default function ListTablePage({
    headerButtons = [],
    filters = [],
    singleName,
    pluralName,
    title,
    columns,
    api
}) {
    const [pageFilters, setFilters] = useState(getInitialFilterState(filters));
    const setFiltersLater = useRef(
        debounce((name, value) =>
            setFilters(prevState => ({...prevState, [name]: value})),
            500
        )
    ).current;

    useEffect(() => {
        return () => {
            setFiltersLater.cancel();
        }
    }, []);

    const handleFilterChange: ChangeEventHandler<HTMLInputElement|HTMLSelectElement> = (event) => {
        setFilters(prevState => ({...prevState, [event.target.name]: event.target.value}));
    }

    const handleDebouncedFilterChange: ChangeEventHandler<HTMLInputElement|HTMLSelectElement> = (event) => {
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
                        filter={filter}
                        onChange={handleFilterChange}
                        debouncedOnChange={handleDebouncedFilterChange}
                    />
                ))}
            </div>
            <div className={styles.pageContent}>
                <ListTable
                    filters={{...pageFilters}}
                    columns={columns}
                    singleName={singleName}
                    pluralName={pluralName}
                    title={title}
                    api={api}
                />
            </div>
        </article>
    );
}

const TableFilter = ({ filter, onChange, debouncedOnChange }) => {
        switch(filter.type){
            case 'select':
                return (
                    <select
                        key={filter.name}
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
                        key={filter.name}
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
    const state = {}
    console.error(filters);
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
