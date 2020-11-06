import './style.scss';

const DonorInfo = () => {
	return (
		<div className="give-donor-profile-donor-info">
			<div className="give-donor-profile-donor-info__avatar">
				<div className="give-donor-profile-donor-info__avatar-container">
					<img src="https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg?cs=srgb&dl=pexels-andrea-piacquadio-774909.jpg&fm=jpg" />
				</div>
			</div>
			<div className="give-donor-profile-donor-info__details">
				<div className="give-donor-profile-donor-info__name">
					San Diego, CA
				</div>
				<div className="give-donor-profile-donor-info__detail">
					PGA Tour
				</div>
				<div className="give-donor-profile-donor-info__detail">
					Last donation 25 days ago
				</div>
				<div className="give-donor-profile-donor-info__detail">
					Donor for 7 months, 6 days
				</div>
			</div>
			<div className="give-donor-profile-donor-info__badges">
				<div className="give-donor-profile-donor-info__badge">
					Recurring Giver
				</div>
				<div className="give-donor-profile-donor-info__badge">
					Team Captain
				</div>
				<div className="give-donor-profile-donor-info__badge">
					Recurring Giver
				</div>
			</div>
		</div>
	);
};
export default DonorInfo;
