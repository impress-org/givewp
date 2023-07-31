<?php
declare(strict_types=1);

namespace Give\Framework\ValidationRules\Rules;

use Closure;
use Give\Framework\Http\Types\FileType;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

/**
 * @unreleased
 */
class File implements ValidationRule
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
     * @unreleased
     */
    public static function id(): string
    {
        return 'file';
    }


    /**
     * @unreleased
     */
    public function maxSize(int $maxSize): ValidationRule
    {
        $this->maxSize = $maxSize;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    /**
     * @unreleased
     */
    public function allowedMimeTypes(array $allowedMimeTypes): ValidationRule
    {
        $this->allowedMimeTypes = $allowedMimeTypes;

        return $this;
    }

    /**
     * @unreleased
     *
     * @return string[]
     */
    public function getAllowedMimeTypes(): array
    {
        return $this->allowedMimeTypes ?? [];
    }

    /**
     * @unreleased
     **/
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        try {
            $fileType = FileType::fromArray($value);

            if (!$fileType->isUploadedFile()) {
                $fail(sprintf(__('%s must be a valid file.', 'give'), '{field}'));
            }

            if (!in_array($fileType->getMimeType(), $this->getAllowedMimeTypes(), true)) {
                $fail(sprintf(__('%s must be a valid file type.', 'give'), '{field}'));
            }

            if ($fileType->getSize() > $this->getMaxSize()) {
                $fail(
                    sprintf(__('%s must be less than or equal to %d bytes.', 'give'), '{field}', $this->getMaxSize())
                );
            }

            if ($fileType->getError() !== UPLOAD_ERR_OK) {
                $fail(sprintf(__('%s must be a valid file.', 'give'), '{field}'));
            }
        } catch (\Throwable $e) {
            $fail($e->getMessage());
        }
    }

    /**
     * @unreleased
     */
    public static function fromString(string $options = null): ValidationRule
    {
        return new self();
    }
}