import PropTypes from 'prop-types'
import './style.scss'

const DonorItem = ({image, name, email, count, total}) => {
    return (
        <div className='donor-item'>
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

DonorItem.propTypes = {
    // Source URL for donor image
    image: PropTypes.string.isRequired,
    // Donor name
    name: PropTypes.string.isRequired,
    // Donor email
    email: PropTypes.string.isRequired,
    // Internationalized count of donations attributed to donor (ex: "2 Donations")
    count: PropTypes.string.isRequired,
    // Internationalized total amount of donations attributed to donor (ex: "$100.00")
    total: PropTypes.string.isRequired
}

DonorItem.defaultProps = {
    image: null,
    name: null,
    email: null,
    count: null,
    total: null
}

export default DonorItem