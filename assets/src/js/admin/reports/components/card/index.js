const Card = (props) => {

    const cardStyle = {
        background: '#fff',
        border: '1px solid #ccc',
        gridColumn: 'span ' + props.width
    }

    const titleStyle = {
        width: '100%',
        borderBottom: '1px solid #ccc',
        padding: '8px 12px',
        fontSize: '18px',
    }

    const contentStyle = {
        width: '100%',
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