import './style.scss';

const Table = ( { header, rows, footer } ) => {
	return (
		<div className="give-donor-profile-table">
			<div className="give-donor-profile-table__header">
				{ header }
			</div>
			<div className="give-donor-profile-table__rows">
				{ rows }
			</div>
			<div className="give-donor-profile-table__footer">
				{ footer }
			</div>
		</div>
	);
};

export default Table;
