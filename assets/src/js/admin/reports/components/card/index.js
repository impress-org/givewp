const Card = (props) => {

    const cardStyle = {
        background: '#fff',
        borderRadius: '5px',
        boxShadow: '0px 3px 6px rgba(68, 68, 68, 0.05), 0px 3px 6px rgba(68, 68, 68, 0.05)',
        gridColumn: 'span ' + props.width,
        display: 'flex',
        flexDirection: 'column',
    }

    const titleStyle = {
        fontWeight: 'bold',
        padding: '16px 12px 12px 12px',
        fontSize: '15px',
    }

    const contentStyle = {
        padding: '12px'
    }

    return (
        <div style={cardStyle}>
            <div style={titleStyle}>
                {props.title}
            </div>
            <div style={contentStyle}>
                {props.children}
            </div>
        </div>
    )
}
export default Card