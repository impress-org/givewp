import PropTypes from 'prop-types'
import './style.scss'

const DonationItem = ({status, amount, time, donor, source}) => {

    const elapsed = '3 hours ago'

    return (
        <div>
            <div>
                {icon}
            </div>
            <div>
                <p>
                    <span>{amount}</span>
                    <span>{status}</span>
                    <span>{elapsed}</span>
                </p>
                <p>
                    {donor.name}<strong>(#{donor.id})</strong>
                </p>
                <p>
                    <span>{source}</span>
                </p>
            </div>
        </div>
    )
}

DonationItem.propTypes = {
    status: PropTypes.string.isRequired,
    amount: PropTypes.string.isRequired,
    time: PropTypes.string.isRequired,
    donor: PropTypes.shape({
        name: PropTypes.string.isRequired,
        id: PropTypes.number.isRequired
    }),
    source: PropTypes.string.isRequired
}

export default DonationItem