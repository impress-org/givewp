// Vendor dependencies
import { Fragment } from 'react';
import PropTypes from 'prop-types';

// Components
import Table from '../table';
import LoadingOverlay from '../loading-overlay';

// Utilities
import { getLabels, getRows } from './utils';

// Store-related dependencies
import { useReportsAPI } from '../../utils';

const RESTTable = ( { title, endpoint } ) => {
	const [ fetched, querying ] = useReportsAPI( endpoint );

	let labels;
	let rows;

	if ( fetched ) {
		labels = getLabels( fetched );
		rows = getRows( fetched );
	}

	const loadingStyle = {
		width: '100%',
		height: '95px',
	};

	return (
		<Fragment>
			{ querying && (
				<LoadingOverlay />
			) }
			{ fetched ? (
				<Table
					title={ title }
					labels={ labels }
					rows={ rows }
				/>
			) : (
				<div style={ loadingStyle } />
			) }
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
