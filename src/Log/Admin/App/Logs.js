import { useState } from 'react';
import { Card, Table, Label, Spinner, Pagination } from 'GiveComponents';
import { useLogFetcher } from './api';

const { __ } = wp.i18n;

const Logs = () => {
	// Get endpoint with additional parameters
	const getEndpoint = ( endpoint ) => {
		const queryString = new URLSearchParams( {
			page: state.currentPage,
			sort: state.sortColumn,
			direction: state.sortDirection,
		} );
		// pretty url?
		const separator = window.wpApiSettings.root.indexOf( '?' ) ? '&' : '?';

		return endpoint + separator + queryString.toString();
	};

	const openModal = ( logId ) => {
		// eslint-disable-next-line no-console
		console.log( logId );
	};

	const setSortDirectionForColumn = ( column, direction ) => {
		setState( ( previousState ) => {
			return {
				...previousState,
				sortColumn: column,
				sortDirection: direction,
			};
		} );
	};

	const setCurrentPage = ( currentPage ) => {
		setState( ( previousState ) => {
			return {
				...previousState,
				currentPage,
			};
		} );
	};

	const [ state, setState ] = useState( {
		initialLoad: false,
		currentPage: 1,
		sortColumn: '',
		sortDirection: '',
		total: 0,
	} );

	const { data, isLoading } = useLogFetcher( getEndpoint( '/get-logs' ), {
		onSuccess: ( { response } ) => {
			setState( ( previousState ) => {
				return {
					...previousState,
					initialLoad: true,
					total: response.total,
				};
			} );
		},
	} );

	const columns = [
		{
			key: 'log_type',
			label: __( 'Status', 'give' ),
			sort: true,
			sortCallback: ( direction ) => setSortDirectionForColumn( 'log_type', direction ),
		},
		{
			key: 'category',
			label: __( 'Category', 'give' ),
			sort: true,
			sortCallback: ( direction ) => setSortDirectionForColumn( 'category', direction ),
		},
		{
			key: 'source',
			label: __( 'Source', 'give' ),
			sort: true,
			sortCallback: ( direction ) => setSortDirectionForColumn( 'source', direction ),
		},
		{
			key: 'date',
			label: __( 'Date/Time', 'give' ),
		},
		{
			key: 'message',
			label: __( 'Description', 'give' ),
		},
		{
			key: 'details',
			label: __( 'Details', 'give' ),
			append: true,
		},
	];

	const columnFilters = {
		log_type: ( type ) => <Label type={ type } />,
		details: ( value, row ) => {
			return (
				<div className="button" onClick={ () => openModal( row.id ) }>
					<span className="dashicons dashicons-visibility"></span>
				</div>
			);
		},
	};

	// Initial load
	if ( ! state.initialLoad && isLoading ) {
		return (
			<Card>
				<div style={ { padding: 50, textAlign: 'center' } }>
					<Spinner size="medium" />
				</div>
			</Card>
		);
	}

	return (
		<>
			<Card>
				<Table
					columns={ columns }
					data={ data }
					columnFilters={ columnFilters }
					isLoading={ isLoading }
					stripped={ false }
				/>
			</Card>

			<Pagination
				currentPage={ state.currentPage }
				setPage={ setCurrentPage }
				totalPages={ state.total }
				disabled={ isLoading }
			/>
		</>
	);
};

export default Logs;
