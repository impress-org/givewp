import styles from "./ListTablePage.module.scss";
import {FormSelect} from "@givewp/components/ListTable/FormSelect";
import React from "react";

export const Filter = ({ filter, onChange, debouncedOnChange }) => {
    switch(filter.type){
        case 'select':
            return (
                <select
                    name={filter.name}
                    className={styles.statusFilter}
                    aria-label={filter?.ariaLabel}
                    onChange={(event) => onChange(event.target.name, event.target.value)}
                >
                    {filter.options.map(({value, text}) => (
                        <option key={value} value={value}>
                            {text}
                        </option>
                    ))}
                </select>
            );
        case 'formselect':
            return (
                <FormSelect
                    name={filter.name}
                    options={filter.options}
                    aria-label={filter?.ariaLabel}
                    placeholder={filter?.text}
                    onChange={onChange}
                />
            );
        case 'search':
            return (
                <input
                    type="search"
                    name={filter.name}
                    aria-label={filter?.ariaLabel}
                    placeholder={filter?.text}
                    onChange={(event) => debouncedOnChange(event.target.name, event.target.value)}
                    className={styles.searchInput}
                />
            );
        default:
            return null;
            break;
    }
}

export const getInitialFilterState = (filters) => {
    const state = {};
    filters.map((filter) => {
        switch (filter.type) {
            case 'select':
            case 'formselect':
                state[filter.name] = filter.options?.[0].value
                break;
            case 'search':
            default:
                state[filter.name] = '';
                break;
        }
    });
    return state;
}
