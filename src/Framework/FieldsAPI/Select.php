<?php

namespace Give\Framework\FieldsAPI;

/**
 * @since 2.12.0
 */
class Select extends Field
{
    use Concerns\AllowMultiple;
    use Concerns\HasEmailTag;
    use Concerns\HasHelpText;
    use Concerns\HasLabel;
    use Concerns\HasOptions;
    use Concerns\HasPlaceholder;
    
    const TYPE = 'select';
}
