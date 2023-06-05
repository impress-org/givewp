<?php

namespace Give\Framework\FieldsAPI;

/**
 * @since 2.12.0
 */
class Hidden extends Field
{

    use Concerns\HasLabel;
    use Concerns\HasEmailTag;
    use Concerns\ShowInReceipt;
    use Concerns\ShowInAdmin;
    use Concerns\StoreAsMeta;

    const TYPE = 'hidden';
}
