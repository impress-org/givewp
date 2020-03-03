export function getBGColor( name ) {
	const palette = [
		'#D75A4B',
		'#F49420',
		'#69B868',
		'#556E79',
		'#9EA3A8',
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
