import { Fragment } from 'react';

import Table from '../table';
import SubscriptionRow from './subscription-row';

const RESTSubscriptionTable = () => {
	return (
		<Table
			header={
				<Fragment>
					<div className="give-donor-profile-table__column">
						Subscription
					</div>
					<div className="give-donor-profile-table__column">
						Status
					</div>
					<div className="give-donor-profile-table__column">
						Next Renewal
					</div>
					<div className="give-donor-profile-table__column">
						Progress
					</div>
				</Fragment>
			}

			rows={
				<Fragment>
					<SubscriptionRow />
					<SubscriptionRow />
					<SubscriptionRow />
					<SubscriptionRow />
					<SubscriptionRow />
				</Fragment>
			}

			footer={
				<Fragment>
					<div className="give-donor-profile-table__footer-text">
						Showing 1-5 of 10 Subscriptions
					</div>
					<div className="give-donor-profile-table__footer-nav">
						Buttons
					</div>
				</Fragment>
			}
		/>
	);
};

export default RESTSubscriptionTable;
