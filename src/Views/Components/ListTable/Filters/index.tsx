import {FormSelect} from '@givewp/components/ListTable/FormSelect';
import Select from '@givewp/components/ListTable/Select';
import Input from '@givewp/components/ListTable/Input';
import {__} from '@wordpress/i18n';
import styles from './Filters.module.scss';

/**
 * @unrelesased updated to use FilterContainer for consistent styling
 */
export const Filter = ({filter, value = null, onChange, debouncedOnChange}) => {
    switch (filter.type) {
        case 'select':
            return (
                <FilterContainer id={'select'}>
                    <Select
                        name={filter.name}
                        aria-label={filter?.ariaLabel}
                        onChange={(event) => onChange(event.target.name, event.target.value)}
                        style={{inlineSize: filter?.inlineSize}}
                        defaultValue={value}
                    >
                        {filter.options.map(({value, text}) => (
                            <option key={value} value={value}>
                                {text}
                            </option>
                        ))}
                    </Select>
                </FilterContainer>
            );
        case 'formselect':
            return (
                <FilterContainer id={'form'} useArrow={true}>
                    <FormSelect
                        name={filter.name}
                        options={filter.options}
                        aria-label={filter?.ariaLabel}
                        placeholder={filter?.text}
                        onChange={onChange}
                        style={{inlineSize: filter?.inlineSize}}
                        defaultValue={value}
                    />
                </FilterContainer>
            );
        case 'search':
            return (
                <FilterContainer id={'search'}>
                    <Input
                        type="search"
                        name={filter.name}
                        aria-label={filter?.ariaLabel}
                        placeholder={filter?.text}
                        onChange={(event) => debouncedOnChange(event.target.name, event.target.value)}
                        style={{inlineSize: filter?.inlineSize}}
                        defaultValue={value}
                    />
                </FilterContainer>
            );
        default:
            return null;
            break;
    }
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

/**
 * @unrelesased
 */
type FilterContainerProps = {
    children: React.ReactNode;
    id: string;
    useArrow?: boolean;
}

/**
 * @unrelesased
 */
export function FilterContainer({children, id, useArrow}: FilterContainerProps) {
    return (
    <div id={id} className={styles.filterContainer}>
         <div className={styles.filter}>
            {children}
            {useArrow && (
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.41 6.912a.833.833 0 0 1 1.179 0l4.41 4.41 4.411-4.41a.833.833 0 1 1 1.179 1.179l-5 5a.833.833 0 0 1-1.179 0l-5-5a.833.833 0 0 1 0-1.179z" fill="#060C1A"/>
                </svg>
            )}
        </div>
    </div>
    );
}