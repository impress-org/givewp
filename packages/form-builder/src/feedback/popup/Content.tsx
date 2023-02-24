const Content = ({children}) => {
    return (
        <div
            style={{
                fontSize: '14px',
                fontWeight: 400,
                lineHeight: '19.6px',
                display: 'flex',
                flexDirection: 'column',
                gap: '1rem',
            }}
        >
            {children}
        </div>
    );
}

export default Content;
