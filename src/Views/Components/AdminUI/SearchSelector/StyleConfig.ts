export const StyleConfig = {
    control: (provided, state) => ({
        ...provided,
        outline: 'none',
        background: '#fff',
        borderRadius: 4,
        boxShadow: '0 2px 4px 0 #ebebeb',
    }),
    indicatorsContainer: (provided, state) => ({
        ...provided,
        background: '#fff',
        height: '100%',
        borderRadius: 4,
    }),
    valueContainer: (provided, state) => ({
        ...provided,
        background: '#fff',
        height: '100%',
        borderRadius: 4,
    }),
    option: (provided, state) => ({
        ...provided,
        backgroundColor: state.isSelected ? '#F2F9FF' : '#FFF',
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
};
