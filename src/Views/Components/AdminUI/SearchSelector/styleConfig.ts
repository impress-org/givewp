export const StyleConfig = {
    control: (provided, state) => ({
        ...provided,
        border: 'none',
        outline: 'none',
        height: 32,
        background: '#fff',
        borderRadius: 2,
        boxShadow: '0 2px 4px 0 #ebebeb',
    }),
    indicatorsContainer: (provided, state) => ({
        ...provided,
        background: '#fff',
        height: '100%',
        borderRadius: 2,
        padding: '0  .45rem',
    }),
    valueContainer: (provided, state) => ({
        ...provided,
        background: '#fff',
        height: '100%',
        width: '15rem',
        padding: '0  4rem',
        borderRadius: 2,
    }),
};
