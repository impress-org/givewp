import { useState } from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { LoadingOverlay } from 'GiveComponents';

import styles from './style.module.scss';

const Table = ( { title, columns, data, columnFilters, stripped, isLoading } ) => {
	const [ state, setState ] = useState( {} );
	const [ cachedData, setCachedData ] = useState( [] );

	Table.resetSortState = () => {
		setState( {} );
	};

	// Clear cache if data is empty
	if ( ! isLoading && ! data.length && cachedData.length ) {
		setCachedData( [] );
	} else if ( data.length && data !== cachedData ) {
		// Cache data so we can show that under overlay while new data is fetching
		setCachedData( data );
	}

	// Display cached data while fetching new data
	const allowedColumns = columns.map( ( column ) => column.key );
	// Get additional row columns added manually
	const additionalColumns = columns
		.filter( ( column ) => 'append' in column && column.append )
		.map( ( column ) => {
			return {
				[ column.key ]: column.defaultValue ?? '',
			};
		} );

	// Used when additional columns are added, but they not exist in the result row.
	// So we have to sort result row to match columns order
	const sortResults = ( order, object ) => {
		const newObject = {};

		order.forEach( ( key ) => {
			newObject[ key ] = object[ key ];
		} );
		return newObject;
	};

	const handleItemSort = ( item ) => {
		const direction = ( state[ item.label ] === 'desc' ) ? 'asc' : 'desc';

		setState( { [ item.label ]: direction } );

		return item.sortCallback( direction );
	};

	const getItemSortDirectionIcon = ( item ) => {
		if ( ! state[ item.label ] ) {
			return <span className={ classNames( 'dashicons dashicons-sort', styles.sortIcons, styles.sortIconUndefined ) } />;
		}

		const iconClasses = classNames(
			'dashicons',
			styles.sortIcons,
			{ 'dashicons-arrow-down': state[ item.label ] === 'desc' },
			{ 'dashicons-arrow-up': state[ item.label ] === 'asc' },
		);

		return <span className={ iconClasses } />;
	};

	const getHeaderRow = () => {
		return columns.map( ( item, index ) => {
			const columnStyles = ( item.styles ) ? { style: item.styles } : null;
			return (
				<div className={ styles.label } key={ index } { ...columnStyles }>
					{ item.label }
					{ item.sort && ( typeof item.sortCallback === 'function' ) && (
						<span onClick={ () => handleItemSort( item ) }>
							{ getItemSortDirectionIcon( item ) }
						</span>
					) }
				</div>
			);
		} );
	};

	const getRows = () => {
		if ( ! isLoading && data.length === 0 ) {
			return (
				<div className={ styles.noData }>
					No data
				</div>
			);
		}

		if ( cachedData.length && isLoading ) {
			data = cachedData;
		}

		return data.map( ( row, index ) => {
			// Add additional row columns and sort the result row
			const result = ( additionalColumns.length > 0 ) ? sortResults( allowedColumns, Object.assign( row, ...additionalColumns ) ) : row;
			const RowItems = Object.entries( result )
				// Display only provided columns
				.filter( ( [ key ] ) => allowedColumns.includes( key ) )
				.map( ( [ key, value ] ) => {
					if ( columnFilters[ key ] && typeof columnFilters[ key ] === 'function' ) {
						value = columnFilters[ key ]( value, data[ index ] );
					}

					const currentColumn = columns.find( ( column ) => column.key === key );
					const columnStyles = ( currentColumn.styles ) ? { style: currentColumn.styles } : null;

					return (
						<div className={ styles.item } key={ key } { ...columnStyles }>
							{ value }
						</div>
					);
				} );

			return (
				<div className={ stripped ? styles.rowStripped : styles.row } key={ index }>
					{ RowItems }
				</div>
			);
		} );
	};

	return (
		<>
			{ isLoading && (
				<LoadingOverlay spinnerSize="small" />
			) }
			{ title && ( <div className={ styles.title }>
				{ title }
			</div> ) }
			<div className={ styles.table }>
				<div className={ classNames( styles.header, { [ styles.headerStripped ]: stripped } ) }>
					{ getHeaderRow() }
				</div>
				{ getRows() }
			</div>
		</>
	);
};

Table.propTypes = {
	// Table title
	title: PropTypes.string,
	// Columns to display
	columns: PropTypes.array.isRequired,
	// Table data rows
	data: PropTypes.array.isRequired,
	// Column filters
	columnFilters: PropTypes.array,
	// Stripped rows
	stripped: PropTypes.bool,
	// Show spinner if data is loading
	isLoading: PropTypes.bool,
};

Table.defaultProps = {
	title: null,
	columns: [],
	data: [],
	columnFilters: [],
	stripped: true,
	isLoading: false,
};

export default Table;
