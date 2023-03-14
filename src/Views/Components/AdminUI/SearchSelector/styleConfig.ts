export const StyleConfig = {
    control: (provided, state) => ({
        ...provided,
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
    }),
    valueContainer: (provided, state) => ({
        ...provided,
        background: '#fff',
        height: '100%',
        borderRadius: 2,
    }),
};
