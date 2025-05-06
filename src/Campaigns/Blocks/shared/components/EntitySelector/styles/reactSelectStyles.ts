export const reactSelectStyles = {
    input: (provided, state) => ({
        ...provided,
        height: '3rem',
    }),
    option: (provided, state) => ({
        ...provided,
        paddingTop: '0.8rem',
        paddingBottom: '0.8rem',
        fontSize: '1rem',
    }),
    control: (provided, state) => ({
        ...provided,
        fontSize: '1rem',
    }),
};

export const reactSelectThemeStyles = (theme) => ({
    ...theme,
    colors: {
        ...theme.colors,
        primary: '#27ae60',
    },
});
