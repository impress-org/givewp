<?php

namespace PHPSTORM_META {
    // Allow PhpStorm IDE to resolve return types when calling give( Object_Type::class ) or give( `Object_Type` ).
    override(
        \give(0),
        map([
                '' => '@',
                '' => '@Class',
            ])
    );

    // Return the method call result when using Call
    override(
        \Give\Helpers\Call::invoke(0), type(0)
    );
}
