<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\ValidationRules\Rules\AllowedTypes;
use Give\Framework\ValidationRules\Rules\File as FileRule;
use Give\Vendors\StellarWP\Validation\Rules\Max;

/**
 * A file upload field.
 *
 * @since 2.32.0 Updated to use the new Validation File Rule; added description
 * @since 2.12.0
 * @since 2.23.1 Moved default rule values inline since inherited constructor is final.
 */
class File extends Field
{
    use Concerns\AllowMultiple;
    use Concerns\HasEmailTag;
    use Concerns\HasHelpText;
    use Concerns\HasLabel;
    use Concerns\HasDescription;

    const TYPE = 'file';

    protected $allowedMimeTypes = [];

    /**
     * Set the maximum file size.
     *
     * @deprecated use maxUploadSize() instead
     *
     * @param  int  $maxSize
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
     *
     * @deprecated use getMaxUploadSize() instead
     */
    public function getMaxSize(): int
    {
        if ( ! $this->hasRule('max')) {
            return wp_max_upload_size();
        }

        return $this->getRule('max')->getSize();
    }

    /**
     * Set the maximum file upload size.
     *
     * @since 2.32.0
     */
    public function maxUploadSize(int $maxUploadSize): File
    {
        if ($this->hasRule(FileRule::id())) {
            /** @var FileRule $rule */
            $rule = $this->getRule(FileRule::id());
            $rule->maxSize($maxUploadSize);
        }

        $this->rules((new FileRule())->maxSize($maxUploadSize));

        return $this;
    }

    /**
     * Access the maximum file upload size.
     *
     * @since 2.32.0
     */
    public function getMaxUploadSize(): int
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
     * @since 2.32.0
     *
     * @param  string[]  $allowedMimeTypes
     */
    public function allowedMimeTypes(array $allowedMimeTypes): File
    {
        if ($this->hasRule(FileRule::id())) {
            /** @var FileRule $rule */
            $rule = $this->getRule(FileRule::id());

            $rule->allowedMimeTypes($allowedMimeTypes);
        } else {
            $this->rules((new FileRule())->allowedMimeTypes($allowedMimeTypes));
        }

        $this->allowedMimeTypes = $allowedMimeTypes;

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
        if ( ! $this->hasRule('allowedTypes')) {
            return get_allowed_mime_types();
        }

        /** @var AllowedTypes $rule */
        $rule = $this->getRule('allowedTypes');

        return $rule->getAllowedTypes();
    }
}
