export const StyleConfig = {
    control: (provided, state) => ({
        ...provided,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'flex-start',
        gap: 12,
        border: 'none',
        height: 32,
        width: '100%',
        background: '#fff',
        padding: 0,
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
        width: 150,
        padding: 0,
        borderRadius: 2,
        paddingRight: 5,
    }),
    option: (provided, state) => ({
        ...provided,
        backgroundColor: state.isSelected ? '#F2F9FF' : '#FFF',
        fontWeight: state.isSelected ? '500' : '400',
        fontSize: '14px', // set the font size of the placeholder
    }),
    singleValue: (provided, state) => ({
        ...provided,
        fontSize: '0.875rem',
        fontWeight: 500,
    }),
};
