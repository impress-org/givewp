// Vendor dependencies
import { Fragment } from 'react';
import PropTypes from 'prop-types';
const { __ } = wp.i18n;

// Components
import List from '../list';
import LoadingOverlay from '../loading-overlay';

import './style.scss';

// Utilities
import { getItems } from './utils';

// Store-related dependencies
import { useReportsAPI } from '../../utils';

const RESTList = ( { title, endpoint } ) => {
	const [ fetched, querying ] = useReportsAPI( endpoint );

	const items = fetched ? getItems( fetched ) : null;

	return (
		<Fragment>
			{ querying && (
				<LoadingOverlay />
			) }
			<List title={ title }>
				{ items ? (
					items
				) : (
					<div className="givewp-list-notice">
						<h1>{ __( 'No data found.', 'give' ) }</h1>
					</div>
				) }
			</List>
		</Fragment>
	);
};

RESTList.propTypes = {
	// API endpoint where data is fetched (ex: 'payment-statuses')
	endpoint: PropTypes.string.isRequired,
};

RESTList.defaultProps = {
	endpoint: null,
};

export default RESTList;
