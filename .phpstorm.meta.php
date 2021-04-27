<?php

namespace PHPSTORM_META {
	// Allow PHP Storm Editor to resolve return types when calling give( Object_Type::class ) or give( `Object_type` )
	override(
		\give( 0 ),
		map( [
			'' => '@',
			'' => '@Class',
		] )
	);
}
