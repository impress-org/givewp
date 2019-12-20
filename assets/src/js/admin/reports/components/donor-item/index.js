import './style.scss'

const DonorItem = ({image, name, email, count, total}) => {
    return (
        <div>
            <img src={image} />
            <div>
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