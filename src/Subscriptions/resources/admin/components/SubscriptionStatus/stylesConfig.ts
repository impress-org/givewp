const dot = (color = 'transparent') => ({
    alignItems: 'center',
    display: 'flex',

    ':before': {
        backgroundColor: color,
        borderRadius: 10,
        content: '" "',
        display: 'block',
        marginRight: 8,
        height: 10,
        width: 10,
    },
});

export const stylesConfig = {
    control: (provided, state) => ({
        ...provided,
        padding: '0.5rem 1rem',
        borderColor: state.isFocused
            ? 'var(--givewp-primary-color, #007cba)'
            : 'var(--givewp-border-color, #ddd)',
        boxShadow: state.isFocused
            ? '0 0 0 1px var(--givewp-primary-color, #007cba)'
            : 'none',
        '&:hover': {
            borderColor: 'var(--givewp-border-hover-color, #999)',
        },
    }),
    option: (provided, state) => ({
        ...provided,
        ...dot(state.data.color),
        paddingBottom: '0.5rem',
        cursor: 'pointer',
    }),
    singleValue: (provided, state) => ({
        ...provided,
        ...dot(state.data.color),
        color: 'inherit',
    }),
    menu: (provided) => ({
        ...provided,
        position: 'relative',
        zIndex: 999,
    }),
    menuList: (provided) => ({
        ...provided,
        maxHeight: '160px',
        overflowY: 'auto',
        paddingBottom: 0,
    }),
};
