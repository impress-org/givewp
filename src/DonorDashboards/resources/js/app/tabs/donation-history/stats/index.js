import { useSelector } from '../hooks';
import { useAccentColor } from '../../../hooks';

import { __ } from '@wordpress/i18n';

import './style.scss';
import { Fragment } from 'react';

const Stats = () => {
	const accentColor = useAccentColor();

	const count = useSelector( ( state ) => state.count );
	const revenue = useSelector( ( state ) => state.revenue );
	const average = useSelector( ( state ) => state.average );
	const currency = useSelector( ( state ) => state.currency );

	return (
		<div className="give-donor-dashboard-dashboard__stats">
			{ count && (
				<div className="give-donor-dashboard-dashboard__stat">
					<div className="give-donor-dashboard-dashboard__figure" style={ { color: accentColor } }>
						{ count }
					</div>
					<div className="give-donor-dashboard-dashboard__detail">
						{ __( 'Number of donations', 'give' ) }
					</div>
				</div>
			) }
			{ revenue && (
				<div className="give-donor-dashboard-dashboard__stat">
					<div className="give-donor-dashboard-dashboard__figure" style={ { color: accentColor } }>
						{ currency.position === 'before' ? (
							<Fragment>
								<span className="give-donor-dashboard-dashboard__figure-currency">{ currency.symbol }</span>{ revenue }
							</Fragment>
						) : (
							<Fragment>
								{ revenue }<span className="give-donor-dashboard-dashboard__figure-currency">{ currency.symbol }</span>
							</Fragment>
						) }
					</div>
					<div className="give-donor-dashboard-dashboard__detail">
						{ __( 'Lifetime donations', 'give' ) }
					</div>
				</div>
			) }
			{ average && (
				<div className="give-donor-dashboard-dashboard__stat">
					<div className="give-donor-dashboard-dashboard__figure" style={ { color: accentColor } }>
						{ currency.position === 'before' ? (
							<Fragment>
								<span className="give-donor-dashboard-dashboard__figure-currency">{ currency.symbol }</span>{ average }
							</Fragment>
						) : (
							<Fragment>
								{ average }<span className="give-donor-dashboard-dashboard__figure-currency">{ currency.symbol }</span>
							</Fragment>
						) }					</div>
					<div className="give-donor-dashboard-dashboard__detail">
						{ __( 'Average donation', 'give' ) }
					</div>
				</div>
			) }
		</div>
	);
};
export default Stats;
