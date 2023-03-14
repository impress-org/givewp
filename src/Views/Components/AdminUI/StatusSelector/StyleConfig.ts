export const StyleConfig = {
    control: (provided, state) => ({
        ...provided,
        display: 'flex',
        alignItems: 'center',
        gap: 12,
        border: 'none',
        height: 32,
        background: '#fff',
        borderRadius: 2,
    }),
    indicatorsContainer: (provided, state) => ({
        ...provided,
        background: state.selectProps.menuIsOpen ? '#bfbfbf' : '#F2F2F2',
        height: '100%',
        borderRadius: 2,
        padding: '0  .45rem',
    }),
    valueContainer: (provided, state) => ({
        ...provided,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        background: '#F2F2F2',
        height: '100%',
        padding: '0  5rem',
        borderRadius: 2,
    }),
    option: (provided, state) => ({
        ...provided,
        backgroundColor: state.isSelected ? '#F2F9FF' : '#FFF',
        fontWeight: state.isSelected ? '600' : '400',
        fontSize: '14px', // set the font size of the placeholder
    }),
    singleValue: (provided, state) => ({
        ...provided,
        fontSize: '0.875rem',
        fontWeight: 500,
    }),
};
