<?php
namespace Give\DonationForms\Rules;

use Closure;
use Give\DonationSpam\Exceptions\SpamDonationException;
use Give\Log\Log;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

/**
 * @since 3.16.2
 */
class HoneyPotRule implements ValidationRule
{

    /**
     * @since 3.16.2
     */
    public static function id(): string
    {
        return 'honeypot';
    }

    /**
     * @since 3.16.2
     */
    public static function fromString(string $options = null): ValidationRule
    {
        return new self();
    }

    /**
    * @since 3.16.2
     * @throws SpamDonationException
     */
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        if (!empty($value)) {
            Log::spam('Spam donation detected via Honeypot field.', [
                'formId' => $values['formId'] ?? null,
            ]);

            throw new SpamDonationException(__('Thank you for the submission!', 'give'));
        }
    }
}
