import styles from "@givewp/components/ListTable/ListTablePage.module.scss";
import {__, sprintf} from "@wordpress/i18n";

//TODO: extract SearchableSelect component from FormSelect
export const FormSelect = ({options, name, placeholder = '', ariaLabel = '', onChange}) => {
    return (
        <>
            <input
                type='search'
                className={styles.searchInput}
                list={`giveSearchSelect-${name}`}
                onChange={updateSearchableSelect(options, name, onChange)}
                autoComplete={'off'}
                aria-label={ariaLabel}
                placeholder={placeholder}
            />
            <datalist
                className={styles.statusFilter}
                id={`giveSearchSelect-${name}`}
                onChange={onChange}
            >
                {options.map(({value, text}) => (
                    <option
                        key={`${value}${text}`}
                        value={value === '0' ? text : `${text} (#${value})`}
                    />
                ))}
            </datalist>
        </>
    );
}

const updateSearchableSelect = (options, name, onChange) => {
    return (event) => {
        if(event.target.value === ''){
            onChange(name, 0);
        }
        const selectedIndex = options.findIndex((option) => {
            return event.target.value.endsWith(`(#${option.value})`);
        });
        if(selectedIndex > -1){
            onChange(name, options[selectedIndex].value);
        }
    }
}
