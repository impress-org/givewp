import PropTypes from 'prop-types'
import { getIcon } from './utils'
import './style.scss'

const DonationItem = ({status, amount, time, donor, source}) => {

    const icon = getIcon(status)
    const elapsed = '3 hours ago'

    return (
        <div className='donation-item'>
            <div className='icon'>
                {icon}
            </div>
            <div className='info'>
                <p>
                    <span>{amount} {status}</span> 
                    <span className='elapsed'>{elapsed}</span>
                </p>
                <p className='donor'>
                    {donor.name}<strong>(#{donor.id})</strong>
                </p>
                <p>
                    <span className='source'>{source}</span>
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