import styles from "@givewp/components/ListTable/ListTablePage.module.scss";

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
                    <option key={`${value}${text}`} value={text}>
                        {value === '0' ? '' : `ID ${value}`}
                    </option>
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
            return option.text.toLowerCase() === event.target.value.toLowerCase()
        });
        if(selectedIndex > -1){
            onChange(name, options[selectedIndex].value);
        }
    }
}
