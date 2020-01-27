import './style.scss';

const DataNotice = ( { type } ) => {
	let notice;
	switch ( type ) {
		case 'NOT_FOUND': {
			notice = 'Data not found.';
		}
	}

	return (
		<div className="givewp-data-notice">
			{ notice }
		</div>
	);
};

export default DataNotice;
