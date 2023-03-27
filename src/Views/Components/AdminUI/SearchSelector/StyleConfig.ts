export const StyleConfig = {
    control: (provided, state) => ({
        ...provided,
        display: 'flex',
        alignItems: 'center',
        outline: 'none',
        background: '#fff',
        borderRadius: 4,
        boxShadow: '0 2px 4px 0 #ebebeb',
    }),
    indicatorsContainer: (provided, state) => ({
        ...provided,
        margin: 'auto 0',
        background: '#fff',
        height: '100%',
        borderRadius: 4,
        '& svg > path': {
            stroke: ' grey',
        },
    }),
    valueContainer: (provided, state) => ({
        ...provided,
        background: '#fff',
        height: '100%',
        borderRadius: 4,
    }),
    option: (provided, state) => ({
        ...provided,
        backgroundColor: state.isFocused ? '#F2F9FF' : '#fff',
        color: '#000',
        fontWeight: state.isSelected ? '600' : '400',
        fontSize: '14px',
    }),
    singleValue: (provided, state) => ({
        ...provided,
        fontSize: '0.875rem',
        fontWeight: 500,
    }),
    placeholder: (provided, state) => ({
        ...provided,
        fontSize: '14px',
        fontWeight: '400',
    }),
    menu: (provided, state) => ({
        ...provided,
        marginTop: '1.5rem',
    }),
};
