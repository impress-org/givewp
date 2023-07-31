<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\ValidationRules\Rules\AllowedTypes;
use Give\Framework\ValidationRules\Rules\File as FileRule;

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

    const TYPE = 'file';

    /**
     * Set the maximum file size.
     *
     * @unreleased updated to set max size on file rule
     */
    public function maxSize(int $maxSize): File
    {
        if ($this->hasRule(FileRule::id())) {
            /** @var FileRule $rule */
            $rule = $this->getRule(FileRule::id());
            $rule->maxSize($maxSize);
        }

        // TODO: add support for file:maxSize
        $this->rules((new FileRule())->maxSize($maxSize));

        return $this;
    }

    /**
     * Access the maximum file size.
     *
     * @unreleased updated to get max size from file rule
     */
    public function getMaxSize(): int
    {
        if (!$this->hasRule(FileRule::id())) {
            return wp_max_upload_size();
        }

        /** @var FileRule $rule */
        $rule = $this->getRule(FileRule::id());

        return $rule->getMaxSize();
    }

    /**
     * Set the allowed mime types.
     *
     * @unreleased
     *
     * @param  string[]  $allowedMimeTypes
     */
    public function allowedMimeTypes(array $allowedMimeTypes): File
    {
        if ($this->hasRule(FileRule::id())) {
            /** @var FileRule $rule */
            $rule = $this->getRule(FileRule::id());
            // TODO: add support for file:allowedMimeTypes
            $rule->allowedMimeTypes($allowedMimeTypes);
        } else {
            $this->rules((new FileRule())->allowedMimeTypes($allowedMimeTypes));
        }


        return $this;
    }

    /**
     * Access the allowed mime types.
     *
     * @return string[]
     */
    public function getAllowedMimeTypes(): array
    {
        if (!$this->hasRule(FileRule::id())) {
            return get_allowed_mime_types();
        }

        /** @var FileRule $rule */
        $rule = $this->getRule(FileRule::id());

        return $rule->getAllowedMimeTypes();
    }

    /**
     * Set the allowed file types.
     *
     * @deprecated use allowedMimeTypes() instead
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
     * @deprecated use getAllowedMimeTypes() instead
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
