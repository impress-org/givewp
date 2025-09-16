import {__} from '@wordpress/i18n';
import CustomFilter from '../CustomFilter';

/**
 * Filter type configurations
 * 
 * @unreleased
 */
const filterConfigs = {
    select: {
        isSearchable: false,
        isSelectable: true,
        width: undefined,
        useDebouncedOnChange: false,
    },
    formselect: {
        isSearchable: true,
        isSelectable: true,
        width: '10.375rem',
        useDebouncedOnChange: false,
    },
    search: {
        isSearchable: true,
        isSelectable: false,
        width: '14.48rem',
        useDebouncedOnChange: true,
    },
};

/**
 * @unreleased
 */
export const Filter = ({filter, value = null, onChange, debouncedOnChange}) => {
    const config = filterConfigs[filter.type];
    
    if (!config) {
        return null;
    }

    return (
        <CustomFilter
            name={filter.name}
            options={filter.options}
            aria-label={filter?.ariaLabel}
            placeholder={filter?.text}
            onChange={config.useDebouncedOnChange ? debouncedOnChange : onChange}
            defaultValue={value}
            isSearchable={config.isSearchable}
            isSelectable={config.isSelectable}
            width={config.width}
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
                case 'formselect':
                default:
                    state[filter.name] = '';
                    break;
            }
        }
    });
    return state;
};
