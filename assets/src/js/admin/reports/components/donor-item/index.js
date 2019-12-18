const DonorItem = ({image, name, email, count, total}) => {
    
    const itemStyle = {
        height: '72px',
        display: 'flex',
        alignItems: 'center'
    }

    const imageStyle = {
        height: '36px',
        width: '36px',
        background: '#CCCCCC',
        borderRadius: '50%',
        objectFit: 'cover',
        overflow: 'hidden'
    }

    return (
        <div style={itemStyle}>
            <img src={image} style={imageStyle}/>
            <div style={{flex: 1, marginLeft: '16px'}}>
                <p style={{margin: 0}}><strong>{name}</strong></p>
                <p style={{margin: 0}}>{email}</p>
            </div>
            <div style={{textAlign: 'right'}}>
                <p style={{margin: 0}}>{count}</p>
                <p style={{margin: 0}}>{total}</p>
            </div>
        </div>
    )
}
export default DonorItem