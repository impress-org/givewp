<?php

declare(strict_types=1);

namespace Give\Framework\FieldsAPI;

/**
 * @since 3.0.0
 */
class Password extends Field
{
    use Concerns\HasHelpText;
    use Concerns\HasLabel;
    use Concerns\HasPlaceholder;

    const TYPE = 'password';

    /**
     * @param bool $storeAsDonorMeta
     *
     * @return $this
     */
    public function storeAsDonorMeta(bool $storeAsDonorMeta = true)
    {
        // Do not allow password fields to be stored as donor meta.
        $this->scope = null;

        return $this;
    }
}
