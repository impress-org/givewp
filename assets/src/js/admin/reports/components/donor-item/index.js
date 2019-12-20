import PropTypes from 'prop-types'
import './style.scss'

const DonorItem = ({image, name, email, count, total}) => {
    const palette = [
        '#D75A4B',
        '#F49420',
        '#69B868',
        '#556E79',
        '#9EA3A8',
    ]
    const profile = image ? <img src={image} /> : <div className='donor-initials' style={{backgroundColor: palette[Math.floor(Math.random() * (palette.length))]}}>HH</div>
    return (
        <div className='donor-item'>
            {profile}
            <div className='donor-info'>
                <p><strong>{name}</strong></p>
                <p>{email}</p>
            </div>
            <div className='donor-totals'>
                <p>{count}</p>
                <p>{total}</p>
            </div>
        </div>
    )
}

DonorItem.propTypes = {
    // Source URL for donor image
    image: PropTypes.string,
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