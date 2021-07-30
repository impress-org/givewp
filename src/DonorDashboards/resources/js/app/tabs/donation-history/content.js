import { useLocation, Link } from 'react-router-dom';
import { Fragment } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

import { __ } from '@wordpress/i18n';

import Heading from '../../components/heading';
import Button from '../../components/button';
import DonationReceipt from '../../components/donation-receipt';
import DonationTable from '../../components/donation-table';

import { useSelector } from './hooks';

import './style.scss';

const Content = () => {
	const donations = useSelector( ( state ) => state.donations );
	const querying = useSelector( ( state ) => state.querying );
	const error = useSelector( ( state ) => state.error );

	const location = useLocation();
	const id = location ? location.pathname.split( '/' )[ 2 ] : null;

	const getDonationById = ( donationId ) => {
		const filter = donations.filter( ( donation ) => parseInt( donation.id ) === parseInt( donationId ) ? true : false );
		if ( filter.length ) {
			return filter[ 0 ];
		}
		return null;
	};

	if ( error ) {
		return (
			<Fragment>
				<Heading icon="exclamation-triangle">
					{ __( 'Error', 'give' ) }
				</Heading>
				<p style={ { color: '#6b6b6b' } }>
					{ error }
				</p>
			</Fragment>
		);
	}

	if ( id ) {
		return querying ? (
			<Fragment>
				<Heading>
					{ __( 'Loading...', 'give' ) }
				</Heading>
				<div className="give-donor-dashboard__donation-history-link">
					<Link to="/donation-history">
						<FontAwesomeIcon icon="arrow-left" />  { __( 'Back to Donation History', 'give' ) }
					</Link>
				</div>
			</Fragment>
		) : (
			<Fragment>
				<Heading>
					{ __( 'Donation Receipt', 'give' ) } #{ getDonationById( id ).payment.serialCode }
				</Heading>
				<DonationReceipt donation={ getDonationById( id ) } />
				<div className="give-donor-dashboard__donation-history-footer">
					<Link to="/donation-history">
						<FontAwesomeIcon icon="arrow-left" /> { __( 'Back to Donation History', 'give' ) }
					</Link>
					{ getDonationById( id ).payment.pdfReceiptUrl.length > 0 && (
						<Button icon="file-pdf" href={ getDonationById( id ).payment.pdfReceiptUrl }>
							{ __( 'Download Receipt', 'give' ) }
						</Button>
					) }
				</div>
			</Fragment>
		);
	}

	return querying ? (
		<Fragment>
			<Heading>
				{ __( 'Loading...', 'give' ) }
			</Heading>
			<DonationTable />
		</Fragment>
	) : (
		<Fragment>
			 <Fragment>
				 <Heading>
					  { `${ donations ? Object.entries( donations ).length : 0 } ${ __( 'Total Donations', 'give' ) }` }
				 </Heading>
				 <DonationTable donations={ donations } perPage={ 5 } />
			 </Fragment>
		</Fragment>
	);
};
export default Content;
