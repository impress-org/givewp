const LevelButton = ({children}) => {
    return (
        <div style={{
            fontSize: '18px',
            fontWeight: 500,
            padding: '16px',
            border: '1px solid var(--givewp-neutral-70)',
            borderRadius: '5px',
            backgroundColor: 'var(--givewp-neutral-70)',
            cursor: 'pointer',
        }}>{children}</div>
    );
};

export default LevelButton;
