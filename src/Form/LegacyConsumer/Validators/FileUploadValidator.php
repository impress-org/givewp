<?php

namespace Give\Form\LegacyConsumer\Validators;

use Give\Form\LegacyConsumer\Traits\HasFilesArray;
use Give\Framework\FieldsAPI\File;

use function _n;
use function esc_html__;
use function give_set_error;
use function size_format;

/**
 * @package Give\Form\LegacyConsumer\Validators
 * @since 2.14.0
 */
class FileUploadValidator
{
    use HasFilesArray;

    /**
     * @var array
     */
    private $files;
    /**
     * @var File
     */
    private $field;
    /**
     * @var int
     */
    private $uploadSize;
    /**
     * @var array
     */
    private $uploadedTypes;

    /**
     * @since 2.14.0
     */
    public function __construct(File $field)
    {
        $this->field = $field;
        $this->files = $this->getFiles();

        foreach ($this->files as $file) {
            $this->uploadSize += $file['size'];
            $this->uploadedTypes[] = $file['type'];
        }
    }

    /**
     * @since 2.14.0
     */
    public function __invoke()
    {
        if ( ! $this->files) {
            $this->validateRequired();

            return;
        }

        $this->validateUploadTypes();
        $this->validateUploadSize();
    }

    /**
     * @since 2.14.0
     */
    private function validateRequired()
    {
        if ($this->field->isRequired()) {
            give_set_error(
                "give-{$this->field->getName()}-required-field-missing",
                $this->field->getRequiredError()['error_message']
            );
        }
    }

    /**
     * @since 2.14.0
     */
    private function validateUploadTypes()
    {
        $allowedTypes = $this->field->getAllowedTypes();

        if (array_diff($this->uploadedTypes, $allowedTypes)) {
            give_set_error(
                'field-api-file-upload-allowed-type-error',
                sprintf(
                    esc_html__('Unable to upload file. Allowed file %1$s: %2$s', 'give'),
                    _n('type', 'types', count($allowedTypes), 'give'),
                    array_reduce(
                        array_keys($allowedTypes),
                        function ($initial, $fileType) {
                            $separator = $initial ? ', ' : '';
                            $initial .= $separator . str_replace('|', ', ', $fileType);

                            return $initial;
                        },
                        ''
                    )
                )
            );
        }
    }

    /**
     * @since 2.14.0
     * @since 2.16.0 File size unit update to bytes from mega bytes in logic to get precise result.
     */
    private function validateUploadSize()
    {
        $allowedFileSize = $this->field->getMaxSize();

        if ($allowedFileSize < $this->uploadSize) {
            give_set_error(
                'field-api-file-upload-size-error',
                sprintf(
                    esc_html__('File size exceed upload limit. Maximum file limit is %s', 'give'),
                    size_format($allowedFileSize)
                )
            );
        }
    }
}
