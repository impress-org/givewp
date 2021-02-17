import { useLocation, Link } from 'react-router-dom';
import { Fragment } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

const { __ } = wp.i18n;

import Heading from '../../components/heading';
import Button from '../../components/button';
import DonationReceipt from '../../components/donation-receipt';
import DonationTable from '../../components/donation-table';

import { useSelector } from './hooks';

import './style.scss';

const Content = () => {
	const donations = useSelector( ( state ) => state.donations );
	const querying = useSelector( ( state ) => state.querying );

	const location = useLocation();
	const id = location ? location.pathname.split( '/' )[ 2 ] : null;

	if ( id ) {
		return querying ? (
			<Fragment>
				<Heading>
					{ __( 'Loading...', 'give' ) }
				</Heading>
				<div className="give-donor-profile__donation-history-link">
					<Link to="/donation-history">
						<FontAwesomeIcon icon="arrow-left" />  { __( 'Back to Donation History', 'give' ) }
					</Link>
				</div>
			</Fragment>
		) : (
			<Fragment>
				<Heading>
					{ __( 'Donation', 'give' ) } #{ id }
				</Heading>
				<DonationReceipt donation={ donations[ id ] } />
				<div className="give-donor-profile__donation-history-footer">
					<Link to="/donation-history">
						<FontAwesomeIcon icon="arrow-left" /> { __( 'Back to Donation History', 'give' ) }
					</Link>
					{ donations[ id ].payment.pdfReceiptUrl.length && (
						<Button icon="file-pdf" onClick={ () => window.location = donations[ id ].payment.pdfReceiptUrl }>
							{ __( 'Download Receipt', 'give' ) }
						</Button>
					) }
				</div>
			</Fragment>
		);
	}

	return querying === true ? (
		<Fragment>
			<Heading>
				{ __( 'Loading...', 'give' ) }
			</Heading>
			<DonationTable />
		</Fragment>
	) : (
		<Fragment>
			<Heading>
				{ `${ Object.entries( donations ).length } ${ __( 'Total Donations', 'give' ) }` }
			</Heading>
			<DonationTable donations={ donations } perPage={ 5 } />
		</Fragment>
	);
};
export default Content;
