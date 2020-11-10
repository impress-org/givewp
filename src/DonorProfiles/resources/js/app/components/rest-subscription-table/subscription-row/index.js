const SubscriptionRow = () => {
	return (
		<div className="give-donor-profile-table__row">
			<div className="give-donor-profile-table__column">
				<div className="give-donor-profile-table__subscription-amount">$200 / Monthly</div>
				<div className="give-donor-profile-table__subscription-campaign">Save Friar Tuck</div>
			</div>
			<div className="give-donor-profile-table__column">
				<div className="give-donor-profile-table__subscription-status">
					Active
				</div>
			</div>
			<div className="give-donor-profile-table__column">
				October 24, 2020 <br />
				8:02 AM
			</div>
			<div className="give-donor-profile-table__column">
				10 donations / Ongoing
			</div>
			<div className="give-donor-profile-table__pill">
				<div>
					<a>Update Payment Info</a>
				</div>
				<div>
					<a>View Receipt</a>
				</div>
				<div>
					<a>Cancel Subscription</a>
				</div>
			</div>
		</div>
	);
};

export default SubscriptionRow;
