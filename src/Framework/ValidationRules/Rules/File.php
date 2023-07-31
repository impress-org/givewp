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
    protected $size;

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
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @unreleased
     */
    public function size(int $size): ValidationRule
    {
        $this->size = $size;

        return $this;
    }
    
    /**
     * @unreleased
     **/
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        try {
            $fileType = FileType::fromArray($value);

            if ($fileType->size > $this->getSize()) {
                $fail(sprintf(__('%s must be less than or equal to %d bytes', 'give'), '{field}', $this->getSize()));
            }

            if ($fileType->error !== UPLOAD_ERR_OK) {
                $fail(sprintf(__('%s must be a valid file', 'give'), '{field}'));
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