import {FormSelect} from '@givewp/components/ListTable/FormSelect';
import Select from '@givewp/components/ListTable/Select';
import Input from '@givewp/components/ListTable/Input';
import pageStyles from '@givewp/components/ListTable/ListTablePage/ListTablePage.module.scss';
import {__} from '@wordpress/i18n';
import styles from './Filters.module.scss';

export const Filter = ({filter, value = null, onChange, debouncedOnChange}) => {
    switch (filter.type) {
        case 'select':
            return (
                <div id={styles.select}>
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
                </div>
            );
        case 'formselect':
            return (
                <div id={styles.formselect}>
                    <FormSelect
                        name={filter.name}
                        options={filter.options}
                        aria-label={filter?.ariaLabel}
                        placeholder={filter?.text}
                        onChange={onChange}
                        style={{inlineSize: filter?.inlineSize}}
                        defaultValue={value}
                    />
                </div>
            );
        case 'search':
            return (
                <div id={styles.search}>
                    <Input
                        type="search"
                        name={filter.name}
                        aria-label={filter?.ariaLabel}
                        placeholder={filter?.text}
                        onChange={(event) => debouncedOnChange(event.target.name, event.target.value)}
                        style={{inlineSize: filter?.inlineSize}}
                        defaultValue={value}
                    />
                    <button className={pageStyles.addFormButton}>{__('Search', 'give')}</button>
                </div>
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
