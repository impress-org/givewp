<?php

namespace Give\Framework\FieldsAPI;

use function get_allowed_mime_types;
use function wp_max_upload_size;

/**
 * A file upload field.
 *
 * @since 2.12.0
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
     * @since 2.16.0 File size unit is bytes, so no need to convert WordPress max file upload size to kilo bytes.
     *
     * @param string $name
     *
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $this->validationRules->rule('maxSize', wp_max_upload_size()); // in bytes
        $this->validationRules->rule('allowedTypes', get_allowed_mime_types());
    }

    /**
     * Set the maximum file size.
     *
     * @param int $maxSize
     *
     * @return $this
     */
    public function maxSize($maxSize)
    {
        $this->validationRules->rule('maxSize', $maxSize);

        return $this;
    }

    /**
     * Access the maximum file size.
     *
     * @return int
     */
    public function getMaxSize()
    {
        return $this->validationRules->getRule('maxSize');
    }

    /**
     * Set the allowed file types.
     *
     * @param string[] $allowedTypes
     *
     * @return $this
     */
    public function allowedTypes($allowedTypes)
    {
        $this->validationRules->rule('allowedTypes', $allowedTypes);

        return $this;
    }

    /**
     * Access the allowed file types.
     *
     * @return string[]
     */
    public function getAllowedTypes()
    {
        return $this->validationRules->getRule('allowedTypes');
    }
}
