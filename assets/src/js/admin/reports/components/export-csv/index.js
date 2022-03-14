import { __ } from '@wordpress/i18n';
import { CSVLink } from 'react-csv';
import './style.scss';

const ExportCSV = ( { filename, headers, rows } ) => {
	const data = [
		headers,
		...rows,
	];

	return (
		<CSVLink filename={ `${ filename }.csv` } data={ data } className="givewp-export-button">{ __( 'Export CSV', 'give' ) }</CSVLink>
	);
};
export default ExportCSV;
