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
    image: PropTypes.string.isRequired,
    name: PropTypes.string.isRequired,
    email: PropTypes.string.isRequired,
    count: PropTypes.string.isRequired,
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