export function getSkeletonLabels() {
	const labels = [
		'Date',
		'Donors',
		'Donations',
		'Refunds',
		'Net',
	];
	return labels;
}

export function getSkeletonRows() {
	const rows = [
		[
			'Feb 1',
			'12',
			'33',
			'$400.00',
			'2',
			'$383.00',
		],
		[
			'Feb 1',
			'12',
			'33',
			'$400.00',
			'2',
			'$383.00',
		],
		[
			'Feb 1',
			'12',
			'33',
			'$400.00',
			'2',
			'$383.00',
		],
		[
			'Feb 1',
			'12',
			'33',
			'$400.00',
			'2',
			'$383.00',
		],
		[
			'Feb 1',
			'12',
			'33',
			'$400.00',
			'2',
			'$383.00',
		],
		[
			'Feb 1',
			'12',
			'33',
			'$400.00',
			'2',
			'$383.00',
		],
		[
			'Feb 1',
			'12',
			'33',
			'$400.00',
			'2',
			'$383.00',
		],
	];
	return rows;
}

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
