import { useSelector } from '../hooks';
import { useAccentColor } from '../../../hooks';

const { __ } = wp.i18n;

import './style.scss';

const Stats = () => {
	const accentColor = useAccentColor();

	const count = useSelector( ( state ) => state.count );
	const revenue = useSelector( ( state ) => state.revenue );
	const average = useSelector( ( state ) => state.average );

	return (
		<div className="give-donor-profile-dashboard__stats">
			{ count && (
				<div className="give-donor-profile-dashboard__stat">
					<div className="give-donor-profile-dashboard__figure" style={ { color: accentColor } }>
						{ count }
					</div>
					<div className="give-donor-profile-dashboard__detail">
						{ __( 'Number of donations', 'give' ) }
					</div>
				</div>
			) }
			{ revenue && (
				<div className="give-donor-profile-dashboard__stat">
					<div className="give-donor-profile-dashboard__figure" style={ { color: accentColor } }>
						<span className="give-donor-profile-dashboard__figure-currency">$</span>{ revenue }
					</div>
					<div className="give-donor-profile-dashboard__detail">
						{ __( 'Lifetime donations', 'give' ) }
					</div>
				</div>
			) }
			{ average && (
				<div className="give-donor-profile-dashboard__stat">
					<div className="give-donor-profile-dashboard__figure" style={ { color: accentColor } }>
						<span className="give-donor-profile-dashboard__figure-currency">$</span>{ average }
					</div>
					<div className="give-donor-profile-dashboard__detail">
						{ __( 'Average donation', 'give' ) }
					</div>
				</div>
			) }
		</div>
	);
};
export default Stats;
