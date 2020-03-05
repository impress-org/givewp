import PropTypes from 'prop-types';
import './style.scss';

const LocationItem = ( { city, state, country, flag, count, total } ) => {
	return (
		<div className="location-item">
			<img className="flag" src={ flag } />
			<div className="info">
				<p><strong>{ city }{ state && ( ', ' + state ) }</strong></p>
				<p>{ country }</p>
			</div>
			<div className="donations">
				<p>{ count }</p>
				<p>{ total }</p>
			</div>
		</div>
	);
};

LocationItem.propTypes = {
	// City name
	city: PropTypes.string.isRequired,
	// State name (not required)
	state: PropTypes.string,
	// Country name
	country: PropTypes.string.isRequired,
	// Flag image URL
	flag: PropTypes.string.isRequired,
	// Internationalized number of donations attributed to location (ex: "2 Donations")
	count: PropTypes.string.isRequired,
	// Internationalized total amount of donations attributed to lcoation (ex: "$345.00")
	total: PropTypes.string.isRequired,
};

LocationItem.defaultProps = {
	city: null,
	state: null,
	country: null,
	flag: null,
	count: null,
	total: null,
};

export default LocationItem;
