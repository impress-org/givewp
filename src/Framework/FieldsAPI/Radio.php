<?php

namespace Give\Framework\FieldsAPI;

/**
 * @unreleased added description
 * @since 2.12.0
 */
class Radio extends Field
{
    use Concerns\HasEmailTag;
    use Concerns\HasHelpText;
    use Concerns\HasLabel;
    use Concerns\HasOptions;
    use Concerns\HasPlaceholder;
    use Concerns\HasDescription;
    
    const TYPE = 'radio';
}
