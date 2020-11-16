import { useSelector } from '../hooks';
import './style.scss';

const Stats = () => {
	const count = useSelector( ( state ) => state.count );
	const revenue = useSelector( ( state ) => state.revenue );
	const average = useSelector( ( state ) => state.average );

	return (
		<div className="give-donor-profile-dashboard__stats">
			{ count && (
				<div className="give-donor-profile-dashboard__stat">
					<div className="give-donor-profile-dashboard__figure">
						{ count }
					</div>
					<div className="give-donor-profile-dashboard__detail">
						Number of donations
					</div>
				</div>
			) }
			{ revenue && (
				<div className="give-donor-profile-dashboard__stat">
					<div className="give-donor-profile-dashboard__figure">
						<span className="give-donor-profile-dashboard__figure-currency">$</span>{ revenue }
					</div>
					<div className="give-donor-profile-dashboard__detail">
						Lifetime donations
					</div>
				</div>
			) }
			{ average && (
				<div className="give-donor-profile-dashboard__stat">
					<div className="give-donor-profile-dashboard__figure">
						<span className="give-donor-profile-dashboard__figure-currency">$</span>{ average }
					</div>
					<div className="give-donor-profile-dashboard__detail">
						Average donation
					</div>
				</div>
			) }
		</div>
	);
};
export default Stats;
