import PropTypes from 'prop-types';
import moment from 'moment';
import { getIcon, getLabel, getColor } from './utils';
import './style.scss';
import { __ } from '@wordpress/i18n';

const DonationItem = ( { status, amount, time, donor, source, url } ) => {
	const icon = getIcon( status );
	const label = getLabel( status );
	const elapsed = moment( time ).toNow( true ) + ' ' + __( 'ago', 'give' );

	return (
		<a className="givewp-donation-link" href={ url }>
			<div className="givewp-donation-item">
				<div className="icon">
					{ icon }
				</div>
				<div className="info">
					<p>
						<span style={ { fontWeight: 'bold', color: getColor( status ) } }>{ amount } { label }</span>
					</p>
					<p>
						<span className="elapsed">{ elapsed }</span>
					</p>
				</div>
				<div className="info">
					<p className="donor">
						{ donor.name }<strong> (#{ donor.id })</strong>
					</p>
					<p>
						<span className="source">{ source }</span>
					</p>
				</div>
			</div>
		</a>
	);
};

DonationItem.propTypes = {
	// Status of donation (either 'completed', 'abandoned', or 'refunded')
	status: PropTypes.string.isRequired,
	// Internationalized amount of the donation (ex: $100.00)
	amount: PropTypes.string.isRequired,
	// String representation of the time a donation ocurred (ex: '2013-02-08 09:30')
	time: PropTypes.string.isRequired,
	// Object describing the donor, including donor name and id
	donor: PropTypes.shape( {
		name: PropTypes.string.isRequired,
		id: PropTypes.oneOfType( [
			PropTypes.string,
			PropTypes.number,
		] ),
	} ),
	// Name of form or campaign where donation originated
	source: PropTypes.string.isRequired,
};

DonationItem.defaultProps = {
	status: null,
	amount: null,
	time: null,
	donor: null,
	source: null,
};

export default DonationItem;
