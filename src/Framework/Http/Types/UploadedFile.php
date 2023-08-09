<?php

namespace Give\Framework\Http\Types;

/**
 * The represents the shape of a file from a POST request.
 *
 * @see https://www.php.net/manual/en/reserved.variables.files.php
 *
 * @unreleased
 */
class UploadedFile
{
    /**
     * The original name of the file on the client machine.
     *
     * @var string
     */
    protected $name;
    /**
     * The mime type of the file, if the browser provided this information. An example would be "image/gif". This mime type is however not checked on the PHP side and therefore don't take its value for granted
     *
     * @var string
     */
    protected $browserMimeType;
    /**
     * The temporary filename of the file in which the uploaded file was stored on the server.
     *
     * @var string
     */
    protected $temporaryName;
    /**
     * The error code associated with this file upload.
     *
     * @see https://www.php.net/manual/en/features.file-upload.errors.php
     * @var int
     */
    protected $error;
    /**
     * The size, in bytes, of the uploaded file
     *
     * @var int
     */
    protected $size;

    /**
     * @unreleased
     */
    public static function fromArray(array $fileArray): UploadedFile
    {
        $file = new self();

        $file->name = (string)$fileArray['name'];
        $file->browserMimeType = (string)$fileArray['type'];
        $file->temporaryName = (string)$fileArray['tmp_name'];
        $file->error = (int)$fileArray['error'];
        $file->size = (int)$fileArray['size'];

        return $file;
    }

    /**
     * @unreleased
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @unreleased
     */
    public function getTemporaryName(): string
    {
        return $this->temporaryName;
    }

    /**
     * @unreleased
     */
    public function getBrowserMimeType(): string
    {
        return $this->browserMimeType;
    }

    /**
     * @unreleased
     *
     * @see https://www.php.net/manual/en/function.is-uploaded-file.php
     */
    public function isUploadedFile(): bool
    {
        return is_uploaded_file($this->temporaryName);
    }

    /**
     * @unreleased
     *
     * @see https://www.php.net/manual/en/function.mime-content-type.php
     */
    public function getMimeType(): string
    {
        return mime_content_type($this->temporaryName);
    }

    /**
     * @unreleased
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @unreleased
     */
    public function getError(): int
    {
        return $this->error;
    }
}