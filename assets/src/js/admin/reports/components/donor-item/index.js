import PropTypes from 'prop-types';
import './style.scss'

const DonorItem = ({image, name, email, count, total}) => {

    return (
        <div class='donor-item'>
            <img src={image} />
            <div>
                <p style={{margin: 0}}><strong>{name}</strong></p>
                <p style={{margin: 0}}>{email}</p>
            </div>
            <div>
                <p style={{margin: 0}}>{count}</p>
                <p style={{margin: 0}}>{total}</p>
            </div>
        </div>
    )
}

DonorItem.propTypes = {
    // URL of donor photo
    image: PropTypes.string,
    // Name of donor
    name: PropTypes.string.isRequired,
    // Donor email
    email: PropTypes.string.isRequired,
    // Internationalized total number of donations attributed to donor (ex: "2 Donations")
    count: PropTypes.string.isRequired,
    // Internationalized total amount of money donated by donor (ex: "$100.00")
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