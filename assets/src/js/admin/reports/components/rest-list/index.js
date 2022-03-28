// Vendor dependencies
import { Fragment } from 'react';
import PropTypes from 'prop-types';
import { __ } from '@wordpress/i18n'

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
						{ ! querying && fetched ? (
							<h1>{ __( 'No data found.', 'give' ) }</h1>
						) : null }
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
