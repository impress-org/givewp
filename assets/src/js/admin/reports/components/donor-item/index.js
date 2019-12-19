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
    image: PropTypes.string,
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