import {__} from '@wordpress/i18n';
import CustomFilter from '../CustomFilter';
import {asyncSelectEntities} from '../CustomFilter/useAsyncCampaigns';
import styles from './styles.module.scss';

/**
 * Filter type configurations
 *
 * @since TBD Add formselect
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
        asyncEntity: asyncSelectEntities.campaign,
    },
    formselect: {
        id: 'formselect',
        isSearchable: true,
        isSelectable: true,
        isClearable: true,
        useDebouncedOnChange: false,
        asyncEntity: asyncSelectEntities.form,
    },
    search: {
        id: 'search',
        isSearchable: true,
        isSelectable: false,
        useDebouncedOnChange: true,
    },
};

/**
 * @since TBD Pass ariaLabel through to the select
 * @since TBD Render formselect as an async select
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
            ariaLabel={filter?.ariaLabel}
            placeholder={filter?.text}
            onChange={config.useDebouncedOnChange ? debouncedOnChange : onChange}
            value={value}
            isSearchable={config.isSearchable}
            isSelectable={config.isSelectable}
            isClearable={config.isClearable}
            isAsync={!!config.asyncEntity}
            asyncEntity={config.asyncEntity}
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
                case 'hidden':
                    state[filter.name] = filter.options?.[0]?.value ?? '';
                    break;
                case 'filterby':
                    filter.groupedOptions.forEach((group) => {
                        if (group.defaultValue) {
                            state[group.id] = [].concat(group.defaultValue);
                        }
                    });
                    break;
                case 'select':
                    state[filter.name] = filter.options?.[0].value;
                    break;
                case 'search':
                case 'campaignselect':
                case 'formselect':
                default:
                    state[filter.name] = '';
                    break;
            }
        }
    });
    return state;
};
