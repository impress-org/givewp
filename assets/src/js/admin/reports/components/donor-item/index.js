import PropTypes from 'prop-types';
import './style.scss';
import { getBGColor, getInitials } from './utils';
import { __ } from '@wordpress/i18n';

const DonorItem = ( { image, name, email, count, total, url } ) => {
	const profile = image ? <img src={ image } /> : <div className="give-donor-initials" style={ { backgroundColor: getBGColor() } }>{ getInitials( name ) }</div>;
	return (
		<a className="give-donor-link" href={ url }>
			<div className="give-donor-item">
				{ profile }
				<div className="give-donor-info">
					<p><strong>{ name }</strong></p>
					<p>{ email }</p>
				</div>
				<div className="give-donor-totals">
					<p>{ count }</p>
					<p>{ total }</p>
				</div>
			</div>
		</a>
	);
};

DonorItem.propTypes = {
	// Source URL for donor image
	image: PropTypes.string,
	// Donor name
	name: PropTypes.string,
	// Donor email
	email: PropTypes.string,
	// Internationalized count of donations attributed to donor (ex: "2 Donations")
	count: PropTypes.string.isRequired,
	// Internationalized total amount of donations attributed to donor (ex: "$100.00")
	total: PropTypes.string.isRequired,
	// Donor Overview URL
	url: PropTypes.string.isRequired,
};

DonorItem.defaultProps = {
	image: null,
	name: __( 'Anonymous', 'give' ),
	email: null,
	count: null,
	total: null,
	url: null,
};

export default DonorItem;
