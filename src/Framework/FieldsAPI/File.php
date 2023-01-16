<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\ValidationRules\Rules\AllowedTypes;

use function get_allowed_mime_types;
use function wp_max_upload_size;

/**
 * A file upload field.
 *
 * @since      2.12.0
 * @since 2.23.1 Moved default rule values inline since inherited constructor is final.
 */
class File extends Field
{

    use Concerns\AllowMultiple;
    use Concerns\HasEmailTag;
    use Concerns\HasHelpText;
    use Concerns\HasLabel;
    use Concerns\ShowInReceipt;
    use Concerns\StoreAsMeta;
    use Concerns\AllowMultiple;

    const TYPE = 'file';

    /**
     * Set the maximum file size.
     *
     * @param int $maxSize
     *
     * @return $this
     */
    public function maxSize($maxSize)
    {
        if ($this->hasRule('max')) {
            /** @var Max $rule */
            $rule = $this->getRule('max');
            $rule->size($maxSize);
        }

        $this->rules("max:$maxSize");

        return $this;
    }

    /**
     * Access the maximum file size.
     */
    public function getMaxSize(): int
    {
        if (!$this->hasRule('max')) {
            return wp_max_upload_size();
        }

        return $this->getRule('max')->getSize();
    }

    /**
     * Set the allowed file types.
     *
     * @param string[] $allowedTypes
     *
     * @return $this
     */
    public function allowedTypes(array $allowedTypes)
    {
        if ($this->hasRule('allowedTypes')) {
            /** @var AllowedTypes $rule */
            $rule = $this->getRule('allowedTypes');
            $rule->setAllowedtypes($allowedTypes);
        }

        $this->rules('allowedTypes:' . implode(',', $allowedTypes));

        return $this;
    }

    /**
     * Access the allowed file types.
     *
     * @return string[]
     */
    public function getAllowedTypes()
    {
        if (!$this->hasRule('allowedTypes')) {
            return get_allowed_mime_types();
        }

        return $this->getRule('allowedTypes')->getAllowedTypes();
    }
}
