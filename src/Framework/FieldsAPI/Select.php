<?php

namespace Give\Framework\FieldsAPI;

/**
 * @unreleased added description
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
    use Concerns\HasDescription;

    const TYPE = 'select';
}
