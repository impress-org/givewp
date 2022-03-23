import styles from "./ListTablePage.module.scss";

export enum FilterPresets {
    Search = 'search',
    Select = 'select',
}

export const Filter = ({ filter, onChange, debouncedOnChange }) => {
    switch(filter.type){
        case 'select':
            return (
                <select
                    name={filter.name}
                    className={styles.statusFilter}
                    aria-label={filter?.ariaLabel}
                    onChange={onChange}
                >
                    {filter.options.map(({value, text}) => (
                        <option key={value} value={value}>
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

export const getInitialFilterState = (filters) => {
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
