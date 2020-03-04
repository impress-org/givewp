export function getBGColor() {
	const palette = [
		'#69B868',
		'#556E79',
		'#9EA3A8',
		'#4BB5D7',
		'#F49420',
		'#D75A4B',
		'#914BD7',
	];
	return palette[ Math.floor( Math.random() * ( palette.length ) ) ];
}

export function getInitials( names ) {
	return names.trim().split( ' ' ).map( singleName => singleName.charAt( 0 ) ).join( '' );
}
