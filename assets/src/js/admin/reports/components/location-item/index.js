import './style.scss'

const LocationItem = ({city, state, country, flag, count, total}) => {
    return (
        <div>
            <img src={flag} />
            <div>
                <p><strong>{city}, {state}</strong></p>
                <p>{country}</p>
            </div>
            <div>
                <p>{count}</p>
                <p>{total}</p>
            </div>
        </div>
    )
}

export default LocationItem