const DonationRow = () => {
	return (
		<div className="give-donor-profile-table__row">
			<div className="give-donor-profile-table__column">
				<div className="give-donor-profile-table__donation-amount">$2,124.40</div>
			</div>
			<div className="give-donor-profile-table__column">
				Save the Whales
			</div>
			<div className="give-donor-profile-table__column">
				October 24, 2020 <br />
				8:02 AM
			</div>
			<div className="give-donor-profile-table__column">
				<div className="give-donor-profile-table__donation-status">
					Renewal
				</div>
			</div>
			<div className="give-donor-profile-table__pill">
				<div className="give-donor-profile-table__donation-id">ID: 4</div>
				<div className="give-donor-profile-table__donation-receipt">
					<a>View Receipt</a>
				</div>
			</div>
		</div>
	);
};

export default DonationRow;
