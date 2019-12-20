import PropTypes from 'prop-types'
import './style.scss'

const LocationItem = ({city, state, country, flag, count, total}) => {
    return (
        <div className='location-item'>
            <img className='flag' src={flag} />
            <div className='info'>
                <p><strong>{city}{state && (', ' + state)}</strong></p>
                <p>{country}</p>
            </div>
            <div className='donations'>
                <p>{count}</p>
                <p>{total}</p>
            </div>
        </div>
    )
}

LocationItem.propTypes = {
    city: PropTypes.string.isRequired,
    state: PropTypes.string,
    country: PropTypes.string.isRequired,
    flag: PropTypes.string.isRequired,
    count: PropTypes.string.isRequired,
    total: PropTypes.string.isRequired
}

LocationItem.defaultProps = {
    city: null,
    state: null,
    country: null,
    flag: null,
    count: null,
    total: null
}

export default LocationItem