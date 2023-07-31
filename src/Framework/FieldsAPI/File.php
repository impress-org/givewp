<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\ValidationRules\Rules\AllowedTypes;
use Give\Framework\ValidationRules\Rules\File as FileRule;

use function get_allowed_mime_types;
use function wp_max_upload_size;

/**
 * A file upload field.
 *
 * @unreleased Updated to use the new Validation File Rule
 * @since 2.12.0
 * @since 2.23.1 Moved default rule values inline since inherited constructor is final.
 */
class File extends Field
{
    use Concerns\AllowMultiple;
    use Concerns\HasEmailTag;
    use Concerns\HasHelpText;
    use Concerns\HasLabel;
    use Concerns\AllowMultiple;

    const TYPE = 'file';

    /**
     * Set the maximum file size.
     */
    public function maxSize(int $maxSize): File
    {
        if ($this->hasRule('file')) {
            /** @var FileRule $rule */
            $rule = $this->getRule('file');
            $rule->size($maxSize);
        }

        // TODO: add support for file:maxSize
        $this->rules((new FileRule())->size($maxSize));

        return $this;
    }

    /**
     * Access the maximum file size.
     */
    public function getMaxSize(): int
    {
        if (!$this->hasRule('file')) {
            return wp_max_upload_size();
        }

        /** @var FileRule $rule */
        $rule = $this->getRule('file');

        return $rule->getSize();
    }

    /**
     * Set the allowed file types.
     *
     * @param  string[]  $allowedTypes
     */
    public function allowedTypes(array $allowedTypes): File
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
    public function getAllowedTypes(): array
    {
        if (!$this->hasRule('allowedTypes')) {
            return get_allowed_mime_types();
        }

        /** @var AllowedTypes $rule */
        $rule = $this->getRule('allowedTypes');

        return $rule->getAllowedTypes();
    }
}
