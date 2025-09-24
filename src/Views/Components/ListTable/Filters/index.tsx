import {__} from '@wordpress/i18n';
import CustomFilter from '../CustomFilter';
import styles from './styles.module.scss';

/**
 * Filter type configurations
 *
 * @since 4.10.0
 */
const filterConfigs = {
    select: {
        id: 'select',
        isSearchable: false,
        isSelectable: true,
        isClearable: true,
        useDebouncedOnChange: false,
    },
    campaignselect: {
        id: 'campaignselect',
        isSearchable: true,
        isSelectable: true,
        isClearable: true,
        useDebouncedOnChange: false,
    },
    search: {
        id: 'search',
        isSearchable: true,
        isSelectable: false,
        useDebouncedOnChange: true,
    },
};

/**
 * @since 4.10.0
 */
export const Filter = ({filter, value = null, onChange, debouncedOnChange}) => {
    const config = filterConfigs[filter.type];

    if (!config) {
        return null;
    }

    if (filter.type === 'search') {
        return (
            <input
                type="search"
                name={filter.name}
                defaultValue={value}
                onChange={(event) => debouncedOnChange(event.target.name, event.target.value)}
                placeholder={filter?.text}
                aria-label={filter?.ariaLabel}
                className={styles.searchInput}
            />
        )
    }

    return (
        <CustomFilter
            name={filter.name}
            options={filter.options}
            aria-label={filter?.ariaLabel}
            placeholder={filter?.text}
            onChange={config.useDebouncedOnChange ? debouncedOnChange : onChange}
            value={value}
            isSearchable={config.isSearchable}
            isSelectable={config.isSelectable}
            isClearable={config.isClearable}
            isAsync={config.id === 'campaignselect'}
        />
    );
};

// figure out what the initial filter state should be based on the filter configuration
export const getInitialFilterState = (filters) => {
    const state = {};
    const urlParams = new URLSearchParams(window.location.search);
    filters.map((filter) => {
        // if the search parameters contained a value for the filter, use that
        const filterQuery = decodeURI(urlParams.get(filter.name));
        // only accept a string or number, we don't want any surprises
        if (urlParams.has(filter.name) && (typeof filterQuery == 'string' || typeof filterQuery == 'number')) {
            state[filter.name] = filterQuery;
        }
        // otherwise, use the default value for the filter type
        else {
            switch (filter.type) {
                case 'select':
                    state[filter.name] = filter.options?.[0].value;
                    break;
                case 'search':
                case 'campaignselect':
                default:
                    state[filter.name] = '';
                    break;
            }
        }
    });
    return state;
};
