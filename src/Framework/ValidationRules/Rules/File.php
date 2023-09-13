<?php
declare(strict_types=1);

namespace Give\Framework\ValidationRules\Rules;

use Closure;
use Give\Framework\Http\Types\UploadedFile;
use Give\Vendors\StellarWP\Validation\Contracts\ValidatesOnFrontEnd;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

/**
 * @since 2.32.0
 */
class File implements ValidationRule, ValidatesOnFrontEnd
{
    /**
     * The size, in bytes, of the uploaded file
     *
     * @var int
     */
    protected $maxSize;

    /**
     * @var string[]
     */
    protected $allowedMimeTypes;

    /**
     * @since 2.32.0
     */
    public static function id(): string
    {
        return 'file';
    }

    /**
     * @since 2.32.0
     */
    public function maxSize(int $maxSize): ValidationRule
    {
        $this->maxSize = $maxSize;

        return $this;
    }

    /**
     * @since 2.32.0
     */
    public function getMaxSize(): int
    {
        return $this->maxSize ?? wp_max_upload_size();
    }

    /**
     * @since 2.32.0
     */
    public function allowedMimeTypes(array $allowedMimeTypes): ValidationRule
    {
        $this->allowedMimeTypes = $allowedMimeTypes;

        return $this;
    }

    /**
     * @since 2.32.0
     *
     * @return string[]
     */
    public function getAllowedMimeTypes(): array
    {
        return $this->allowedMimeTypes ?? get_allowed_mime_types();
    }

    /**
     * @since 2.32.0
     **/
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        try {
            $file = UploadedFile::fromArray($value);

            if (!$file->isUploadedFile()) {
                $fail(sprintf(__('%s must be a valid file.', 'give'), '{field}'));
            }

            // check against both the allowed mime types defined by the file rule and the server
            if (!in_array($file->getMimeType(), $this->getAllowedMimeTypes(), true) ||
                !in_array($file->getMimeType(), get_allowed_mime_types(), true)) {
                $fail(sprintf(__('%s must be a valid file type.', 'give'), '{field}'));
            }

            // check against both the max upload size defined by the file rule and the server
            if ($file->getSize() > $this->getMaxSize() || $file->getSize() > wp_max_upload_size()) {
                $fail(
                    sprintf(__('%s must be less than or equal to %d bytes.', 'give'), '{field}', $this->getMaxSize())
                );
            }

            if ($file->getError() !== UPLOAD_ERR_OK) {
                $fail(sprintf(__('%s must be a valid file.', 'give'), '{field}'));
            }
        } catch (\Throwable $e) {
            $fail($e->getMessage());
        }
    }

    /**
     * @since 2.32.0
     */
    public static function fromString(string $options = null): ValidationRule
    {
        return new self();
    }

    public function serializeOption()
    {
        return null;
    }
}
