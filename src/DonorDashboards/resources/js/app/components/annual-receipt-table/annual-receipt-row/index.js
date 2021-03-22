import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { __ } from '@wordpress/i18n';
;

const AnnualReceiptRow = ( { annualReceipt } ) => {
	const { year, amount, count, statementUrl } = annualReceipt[ 1 ];

	const handleHrefClick = ( e ) => {
		e.preventDefault();
		window.parent.open( e.target.href, '_blank' );
	};

	return (
		<div className="give-donor-dashboard-table__row">
			<div className="give-donor-dashboard-table__column">
				{ year.label }
			</div>
			<div className="give-donor-dashboard-table__column">
				{ amount.formatted }
			</div>
			<div className="give-donor-dashboard-table__column">
				{ count }
			</div>
			<div className="give-donor-dashboard-table__column">
				<a href={ statementUrl } onClick={ ( e ) => handleHrefClick( e ) }>
					{ __( 'View Receipt', 'give' ) } <FontAwesomeIcon icon="arrow-right" />
				</a>
			</div>
		</div>
	);
};

export default AnnualReceiptRow;
