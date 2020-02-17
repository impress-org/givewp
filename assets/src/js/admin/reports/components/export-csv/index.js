const { __ } = wp.i18n;
import { CSVLink } from 'react-csv';
import './style.scss';

const ExportCSV = ( { filename, headers, rows } ) => {
	const data = [
		headers,
	];

	rows.forEach( ( row ) => {
		data.push( row );
	} );

	return (
		<CSVLink filename={ `${ filename }.csv` } data={ data } className="givewp-export-button">{ __( 'Export CSV', 'give' ) }</CSVLink>
	);
};
export default ExportCSV;
