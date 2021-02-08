import { useState } from 'react';
import { Card, Label, LoadingNotice, Pagination, Select, Table, Button, Input } from 'GiveComponents';
import { useLogFetcher } from './api';

import styles from './styles.module.scss';

const { __ } = wp.i18n;

const Logs = () => {
	const [ state, setState ] = useState( {
		initialLoad: false,
		currentPage: 1,
		currentStatus: '', // log type
		currentSource: '',
		currentCategory: '',
		sortColumn: '',
		sortDirection: '',
		startDate: '',
		endDate: '',
		total: 0,
		statuses: [],
		sources: [],
		categories: [],
	} );

	const openModal = ( logId ) => {
		// eslint-disable-next-line no-console
		console.log( logId );
	};

	// GET endpoint with additional parameters
	const getEndpoint = ( endpoint, data ) => {
		if ( data ) {
			const queryString = new URLSearchParams( data );
			// pretty url?
			const separator = window.wpApiSettings.root.indexOf( '?' ) ? '&' : '?';

			return endpoint + separator + queryString.toString();
		}

		return endpoint;
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

	const setCurrentCategory = ( e ) => {
		const category = e.target.value;
		setState( ( previousState ) => {
			return {
				...previousState,
				currentCategory: category,
			};
		} );
	};

	const setCurrentStatus = ( e ) => {
		const status = e.target.value;
		setState( ( previousState ) => {
			return {
				...previousState,
				currentStatus: status,
			};
		} );
	};

	const setCurrentSource = ( e ) => {
		const source = e.target.value;
		setState( ( previousState ) => {
			return {
				...previousState,
				currentSource: source,
			};
		} );
	};

	const setStartDate = ( e ) => {
		const date = e.target.value;
		setState( ( previousState ) => {
			return {
				...previousState,
				startDate: date,
			};
		} );
	};

	const setEndDate = ( e ) => {
		const date = e.target.value;
		setState( ( previousState ) => {
			return {
				...previousState,
				endDate: date,
			};
		} );
	};

	const getCategories = () => {
		const defaultCategory = {
			value: '',
			label: __( 'All categories', 'give' ),
		};

		const categories = Object.values( state.categories ).map( ( label ) => {
			return {
				label,
				value: label,
			};
		} );

		return [ defaultCategory, ...categories ];
	};

	const getStatuses = () => {
		const defaultStatus = {
			value: '',
			label: __( 'All statuses', 'give' ),
		};

		const statuses = Object.entries( state.statuses ).map( ( [ label, value ] ) => {
			return {
				label,
				value,
			};
		} );

		return [ defaultStatus, ...statuses ];
	};

	const getSources = () => {
		const defaultSource = {
			value: '',
			label: __( 'All sources', 'give' ),
		};

		const sources = Object.values( state.sources ).map( ( label ) => {
			return {
				label,
				value: label,
			};
		} );

		return [ defaultSource, ...sources ];
	};

	const resetQueryParameters = () => {
		setState( ( previousState ) => {
			return {
				...previousState,
				currentPage: 1,
				currentStatus: '',
				currentSource: '',
				currentCategory: '',
				sortColumn: '',
				sortDirection: '',
				startDate: '',
				endDate: '',
			};
		} );
	};

	const parameters = {
		page: state.currentPage,
		sort: state.sortColumn,
		direction: state.sortDirection,
		type: state.currentStatus,
		source: state.currentSource,
		category: state.currentCategory,
		start: state.startDate,
		end: state.endDate,
	};

	const { data, isLoading } = useLogFetcher( getEndpoint( '/get-logs', parameters ), {
		onSuccess: ( { response } ) => {
			setState( ( previousState ) => {
				return {
					...previousState,
					initialLoad: true,
					total: response.total,
					statuses: response.statuses,
					categories: response.categories,
					sources: response.sources,
					currentPage: state.currentPage > response.total ? 1 : state.currentPage,
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
				<Button onClick={ () => openModal( row.id ) } icon={ true }>
					<span className="dashicons dashicons-visibility" />
				</Button>
			);
		},
	};

	// Initial load
	if ( ! state.initialLoad && isLoading ) {
		return (
			<LoadingNotice notice={ __( 'Loading log activity', 'give' ) } />
		);
	}

	return (
		<>
			<div className={ styles.headerRow }>

				<Select
					options={ getStatuses() }
					onChange={ setCurrentStatus }
					defaultValue={ state.currentStatus }
					className={ styles.headerItem }
				/>

				<Select
					options={ getCategories() }
					onChange={ setCurrentCategory }
					defaultValue={ state.currentCategory }
					className={ styles.headerItem }
				/>

				<Select
					options={ getSources() }
					onChange={ setCurrentSource }
					defaultValue={ state.currentSource }
					className={ styles.headerItem }
				/>

				<Input
					type="date"
					placeholder={ __( 'Start date', 'give' ) }
					onChange={ setStartDate }
					value={ state.startDate }
					className={ styles.headerItem }
				/>

				<Input
					type="date"
					placeholder={ __( 'End date', 'give' ) }
					onChange={ setEndDate }
					value={ state.endDate }
					className={ styles.headerItem }
				/>

				<Button onClick={ resetQueryParameters }>
					{ __( 'Reset', 'give' ) }
				</Button>

				<div className={ styles.topPagination }>
					<Pagination
						currentPage={ state.currentPage }
						setPage={ setCurrentPage }
						totalPages={ state.total }
						disabled={ isLoading }
					/>
				</div>
			</div>

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
