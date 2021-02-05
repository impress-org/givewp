import { useLocation, Link } from 'react-router-dom';
import { Fragment } from 'react';

const { __ } = wp.i18n;

import Heading from '../../components/heading';
import SubscriptionReceipt from '../../components/subscription-receipt';
import SubscriptionTable from '../../components/subscription-table';

import { useSelector } from './hooks';

const Content = () => {
	const subscriptions = useSelector( ( state ) => state.subscriptions );
	const querying = useSelector( ( state ) => state.querying );

	const location = useLocation();
	const route = location ? location.pathname.split( '/' )[ 2 ] : null;
	const id = location ? location.pathname.split( '/' )[ 3 ] : null;

	if ( id ) {
		switch ( route ) {
			case 'receipt' : {
				return querying ? (
					<Fragment>
						<Heading>
							{ __( 'Loading...', 'give' ) }
						</Heading>
						<Link to="/recurring-donations">
							{ __( 'Back to Recurring Donations', 'give' ) }
						</Link>
					</Fragment>
				) : (
					<Fragment>
						<Heading>
							{ __( 'Subscription', 'give' ) } #{ id }
						</Heading>
						<SubscriptionReceipt subscription={ subscriptions[ id ] } />
						<Link to="/recurring-donations">
							{ __( 'Back to Recurring Donations', 'give' ) }
						</Link>
					</Fragment>
				);
			}
			case 'update-method' : {
				return querying ? (
					<Fragment>
						<Heading>
							{ __( 'Loading...', 'give' ) }
						</Heading>
						<Link to="/recurring-donations">
							{ __( 'Back to Recurring Donations', 'give' ) }
						</Link>
					</Fragment>
				) : (
					<Fragment>
						<Heading>
							{ __( 'Update Payment Method', 'give' ) } #{ id }
						</Heading>
						<SubscriptionReceipt subscription={ subscriptions[ id ] } />
						<Link to="/recurring-donations">
							{ __( 'Back to Recurring Donations', 'give' ) }
						</Link>
					</Fragment>
				);
			}
			case 'cancel' : {
				return querying ? (
					<Fragment>
						<Heading>
							{ __( 'Loading...', 'give' ) }
						</Heading>
						<Link to="/recurring-donations">
							{ __( 'Back to Recurring Donations', 'give' ) }
						</Link>
					</Fragment>
				) : (
					<Fragment>
						<Heading>
							{ __( 'Cancel Subscription', 'give' ) } #{ id }
						</Heading>
						<SubscriptionReceipt subscription={ subscriptions[ id ] } />
						<Link to="/recurring-donations">
							{ __( 'Back to Recurring Donations', 'give' ) }
						</Link>
					</Fragment>
				);
			}
		}
	}

	return querying === true ? (
		<Fragment>
			<Heading>
				{ __( 'Loading...', 'give' ) }
			</Heading>
			<SubscriptionTable />
		</Fragment>
	) : (
		<Fragment>
			<Heading>
				{ `${ Object.entries( subscriptions ).length } ${ __( 'Total Subscriptions', 'give' ) }` }
			</Heading>
			<SubscriptionTable subscriptions={ subscriptions } perPage={ 5 } />
		</Fragment>
	);
};
export default Content;
