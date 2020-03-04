import { Fragment } from 'react';
import ExportCSV from '../export-csv';

import './style.scss';

const Table = ( { title, labels, rows } ) => {
	const labelEls = labels.map( ( label, index ) => {
		return (
			<div className="givewp-table-label" key={ index }>
				{ label }
			</div>
		);
	} );

	const rowEls = rows.map( ( row, index ) => {
		const itemEls = row.map( ( item, key ) => {
			return (
				<div className="givewp-table-row-item" key={ key }>
					{ item }
				</div>
			);
		} );

		return (
			<div className="givewp-table-row" key={ index }>
				{ itemEls }
			</div>
		);
	} );

	let filename = title.replace( ' ', '-' );
	filename = filename.toLowerCase();

	return (
		<Fragment>
			{ title && ( <div className="givewp-table-title">
				{ title }
				<ExportCSV filename={ filename } headers={ labels } rows={ rows } />
			</div> ) }
			<div className="givewp-table">
				<div className="givewp-table-header">
					{ labelEls }
				</div>
				{ rowEls }
			</div>
		</Fragment>
	);
};

export default Table;
