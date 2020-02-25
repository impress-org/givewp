// Vendor dependencies
import { Fragment } from 'react';
import PropTypes from 'prop-types';

// Components
import Table from '../table';
import LoadingOverlay from '../loading-overlay';

// Utilities
import { getSkeletonLabels, getSkeletonRows, getLabels, getRows } from './utils';

// Store-related dependencies
import { useReportsAPI } from '../../utils';

const RESTTable = ( { title, endpoint } ) => {
	const [ fetched, querying ] = useReportsAPI( endpoint );

	let labels;
	let rows;

	if ( fetched ) {
		labels = getLabels( fetched );
		rows = getRows( fetched );
	} else {
		labels = getSkeletonLabels();
		rows = getSkeletonRows();
	}

	return (
		<Fragment>
			{ querying && (
				<LoadingOverlay />
			) }
			<Table
				title={ title }
				labels={ labels }
				rows={ rows }
			/>
		</Fragment>
	);
};

RESTTable.propTypes = {
	// API endpoint where data is fetched (ex: 'payment-statuses')
	endpoint: PropTypes.string.isRequired,
};

RESTTable.defaultProps = {
	endpoint: null,
};

export default RESTTable;
