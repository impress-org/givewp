import { useState } from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { LoadingOverlay } from 'GiveComponents';

import styles from './style.module.scss';

const Table = ( { title, headerRow, columns, data, columnFilters, stripped, isLoading } ) => {
	const [ state, setState ] = useState( {} );

	const handleItemSort = ( item ) => {
		const direction = ( state[ item.label ] === 'desc' ) ? 'asc' : 'desc';

		setState( { [ item.label ]: direction } );

		return item.sortCallback( direction );
	};

	const getItemDirectionIcon = ( item ) => {
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
		return headerRow.map( ( item, index ) => {
			return (
				<div className={ styles.label } key={ index }>
					{ item.label }
					{ item.sort && ( typeof item.sortCallback === 'function' ) && (
						<span onClick={ () => handleItemSort( item ) }>
							{ getItemDirectionIcon( item ) }
						</span>
					) }
				</div>
			);
		} );
	};

	const getRows = () => {
		return data.map( ( row, index ) => {
			const RowItems = Object.entries( row )
				// Only selected columns
				.filter( ( [ key ] ) => columns.includes( key ) )
				.map( ( [ key, value ] ) => {
					if ( columnFilters[ key ] && typeof columnFilters[ key ] === 'function' ) {
						value = columnFilters[ key ]( value, data[ index ] );
					}

					return (
						<div className={ styles.item } key={ key }>
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
				<LoadingOverlay />
			) }
			{ title && ( <div className={ styles.title }>
				{ title }
			</div> ) }
			<div className={ styles.table }>
				{ ( headerRow.length > 0 ) && (
					<div className={ classNames( styles.header, { [ styles.headerStripped ]: stripped } ) }>
						{ getHeaderRow() }
					</div>
				) }
				{ getRows() }
			</div>
		</>
	);
};

Table.propTypes = {
	// Table title
	title: PropTypes.string,
	// Table header columns
	header: PropTypes.array,
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
	header: [],
	columns: [],
	data: [],
	columnFilters: [],
	stripped: true,
	isLoading: false,
};

export default Table;
