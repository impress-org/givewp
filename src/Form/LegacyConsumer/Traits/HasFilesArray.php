<?php

namespace Give\Form\LegacyConsumer\Traits;

use Give\Framework\FieldsAPI\File;

/**
 * @since 2.14.0
 *
 * @property File $field
 */
trait HasFilesArray
{
    /**
     * @since 2.14.0
     * @return array
     */
    public function getFiles()
    {
        $_files = $_FILES[$this->field->getName()]; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- runs during donation validation and saving, after the request was verified. file upload array; it is only handed to the upload code, which uses wp_handle_upload() to check it.
        $files = [];

        if (empty($_files)) {
            return [];
        }

        if ( ! $this->field->getAllowMultiple()) {
            return [$_files];
        }

        foreach ($_files as $key => $data) {
            foreach ($data as $index => $item) {
                $files[$index][$key] = $item;
            }
        }

        return array_filter($files, function ($file) {
            return empty($file['error']);
        });
    }
}
