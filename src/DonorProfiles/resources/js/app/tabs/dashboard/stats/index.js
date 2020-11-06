import './style.scss';

const Stats = () => {
	return (
		<div className="give-donor-profile-dashboard__stats">
			<div className="give-donor-profile-dashboard__stat">
				<div className="give-donor-profile-dashboard__figure">
					4
				</div>
				<div className="give-donor-profile-dashboard__detail">
					Number of donations
				</div>
			</div>
			<div className="give-donor-profile-dashboard__stat">
				<div className="give-donor-profile-dashboard__figure">
					<span className="give-donor-profile-dashboard__figure-currency">$</span>6,240
				</div>
				<div className="give-donor-profile-dashboard__detail">
					Lifetime donations
				</div>
			</div>
			<div className="give-donor-profile-dashboard__stat">
				<div className="give-donor-profile-dashboard__figure">
					<span className="give-donor-profile-dashboard__figure-currency">$</span>2,030
				</div>
				<div className="give-donor-profile-dashboard__detail">
					Average donation
				</div>
			</div>
		</div>
	);
};
export default Stats;
