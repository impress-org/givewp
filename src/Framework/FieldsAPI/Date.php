<?php

namespace Give\Framework\FieldsAPI;

/**
 * @since 2.12.0
 */
class Date extends Field
{
    use Concerns\HasEmailTag;
    use Concerns\HasHelpText;
    use Concerns\HasLabel;
    use Concerns\HasPlaceholder;

    const TYPE = 'date';
}
