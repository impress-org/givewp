const DonorItem = ({image, name, email, count, total}) => {
    
    const itemStyle = {
        height: '72px',
        display: 'flex'
    }

    const imageStyle = {
        height: '36px',
        width: '36px',
        borderRadius: '50%',
        objectFit: 'cover'
    }

    return (
        <div style={itemStyle}>
            <img src={image}/>
            <div style={{flex: 1}}>
                <p><strong>{name}</strong></p>
                <p>{email}</p>
            </div>
            <div>
                <p>{count}</p>
                <p>{total}</p>
            </div>
        </div>
    )
}
export default DonorItem