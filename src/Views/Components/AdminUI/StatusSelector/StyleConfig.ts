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
        background: '#F2F2F2',
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
};
