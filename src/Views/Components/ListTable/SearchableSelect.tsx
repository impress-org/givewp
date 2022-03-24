import styles from "@givewp/components/ListTable/ListTablePage.module.scss";

export const SearchableSelect = ({options, name, placeholder = '', ariaLabel = '', onChange}) => {
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
                    <option key={value} data-value={value}>
                        {text}
                    </option>
                ))}
            </datalist>
        </>
    );
}

const updateSearchableSelect = (options, name, onChange) => {
    return (event) => {
        const selectedIndex = options.findIndex(option => {
            return option.value.toLowerCase() === event.target.value.toLowerCase()
        });
        if(selectedIndex > -1){
            onChange(name, options[selectedIndex].value)
        }
    }
}
