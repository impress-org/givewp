import './style.scss'

const LocationItem = ({city, state, country, flag, count, total}) => {
    return (
        <div className='location-item'>
            <img className='flag' src={flag} />
            <div className='info'>
                <p><strong>{city}, {state}</strong></p>
                <p>{country}</p>
            </div>
            <div className='donations'>
                <p>{count}</p>
                <p>{total}</p>
            </div>
        </div>
    )
}

export default LocationItem