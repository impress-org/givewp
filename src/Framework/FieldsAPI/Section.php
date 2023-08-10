<?php

namespace Give\Framework\FieldsAPI;


/**
 * @unreleased added description
 * @since 2.22.0
 */
class Section extends Group
{
    use Concerns\HasLabel;
    use Concerns\HasDescription;

    /**
     * @since 2.22.0
     */
    const TYPE = 'section';
}
