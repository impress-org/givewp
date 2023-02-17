const LevelGrid = ({children}) => {
    return (
        <div style={{
            textAlign: 'center',
            display: 'grid',
            gap: '14px',
            gridTemplateColumns: '1fr 1fr 1fr',
        }}>{children}</div>
    );
};

export default LevelGrid;
