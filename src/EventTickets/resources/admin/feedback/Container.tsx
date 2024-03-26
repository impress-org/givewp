const Container = ({children}) => {
    return (
        <div style={{
            zIndex: 99999999,
            position: 'fixed',
            bottom: '40px',
            right: '20px',
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'flex-end',
            gap: '10px',
        }}>
            {children}
        </div>
    )
}

export default Container;
