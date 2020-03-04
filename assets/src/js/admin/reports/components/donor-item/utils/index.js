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

export function getInitials( name ) {
	const names = name.split( ' ' );

	if ( names.length > 1 ) {
		return names[ 0 ].charAt( 0 ) + names[ names.length - 1 ].charAt( 0 );
	}
	return names[ 0 ].charAt( 0 );
}
