// Vendor dependencies
import { Fragment } from 'react';
import PropTypes from 'prop-types';

// Components
import List from '../list';
import LoadingOverlay from '../loading-overlay';

// Utilities
import { getItems, getSkeletonItems } from './utils';

// Store-related dependencies
import { useReportsAPI } from '../../utils';

const RESTList = ( { title, endpoint } ) => {
	const [ fetched, querying ] = useReportsAPI( endpoint );

	let items;
	if ( fetched ) {
		items = getItems( fetched );
	} else {
		items = getSkeletonItems();
	}

	return (
		<Fragment>
			{ querying && (
				<LoadingOverlay />
			) }
			<List title={ title }>
				{ items }
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
