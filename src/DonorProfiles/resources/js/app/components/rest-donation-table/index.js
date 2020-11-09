import { Fragment } from 'react';

import Table from '../table';
import DonationRow from './donation-row';

const RESTDonationTable = () => {
	return (
		<Table
			header={
				<Fragment>
					<div className="give-donor-profile-table__column">
						Donation
					</div>
					<div className="give-donor-profile-table__column">
						Campaign
					</div>
					<div className="give-donor-profile-table__column">
						Date
					</div>
					<div className="give-donor-profile-table__column">
						Status
					</div>
				</Fragment>
			}

			rows={
				<Fragment>
					<DonationRow />
					<DonationRow />
					<DonationRow />
					<DonationRow />
					<DonationRow />
				</Fragment>
			}

			footer={
				<Fragment>
					<div className="give-donor-profile-table__footer-text">
						Showing 1-5 of 10 Donations
					</div>
					<div className="give-donor-profile-table__footer-nav">
						Buttons
					</div>
				</Fragment>
			}
		/>
	);
};

export default RESTDonationTable;
