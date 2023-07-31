<?php

namespace Give\Framework\Http\Types;

/**
 * The represents the shape of a file from a POST request.
 *
 * @see https://www.php.net/manual/en/reserved.variables.files.php
 *
 * @unreleased
 */
class FileType {
    /**
     * The original name of the file on the client machine.
     *
     * @var string
     */
    public $name;
    /**
     * The mime type of the file, if the browser provided this information. An example would be "image/gif". This mime type is however not checked on the PHP side and therefore don't take its value for granted
     *
     * @var string
     */
    public $type;
    /**
     * The temporary filename of the file in which the uploaded file was stored on the server.
     *
     * @var string
     */
    public $tmpName;
    /**
     * The error code associated with this file upload.
     *
     * @see https://www.php.net/manual/en/features.file-upload.errors.php
     * @var int
     */
    public $error;
    /**
     * The size, in bytes, of the uploaded file
     *
     * @var int
     */
    public $size;

    /**
     * @unreleased
     */
    public static function fromArray(array $fileArray): FileType
    {
        $file = new self();

        $file->name = (string)$fileArray['name'];
        $file->type = (string)$fileArray['type'];
        $file->tmpName = (string)$fileArray['tmp_name'];
        $file->error = (int)$fileArray['error'];
        $file->size = (int)$fileArray['size'];

        return $file;
    }
}