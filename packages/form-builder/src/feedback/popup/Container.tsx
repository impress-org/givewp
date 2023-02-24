const Container = ({children}) => {
    return (
        <div
            style={{
                position: 'relative',
                border: '1px solid var(--givewp-gray-30)',
                boxShadow: '0px 2px 4px rgba(221, 221, 221, 0.25)',
                borderRadius: '5px',
                backgroundColor: 'white',
                padding: '1rem',
                width: '324px',
                display: 'flex',
                gap: '12px',
                flexDirection: 'column',
            }}
        >
            {children}
        </div>
    );
}

export default Container;
