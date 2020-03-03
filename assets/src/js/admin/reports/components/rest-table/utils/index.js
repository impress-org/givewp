export function getLabels( data ) {
	const labels = Object.keys( data[ 0 ] );
	return labels;
}

export function getRows( data ) {
	const rows = data.map( ( row ) => {
		return Object.values( row );
	} );
	return rows;
}
