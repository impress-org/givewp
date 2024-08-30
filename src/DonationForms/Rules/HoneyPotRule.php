<?php
namespace Give\DonationForms\Rules;

use Closure;
use Give\DonationSpam\Exceptions\SpamDonationException;
use Give\Log\Log;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

/**
 * @unreleased
 */
class HoneyPotRule implements ValidationRule
{

    /**
     * @unreleased
     */
    public static function id(): string
    {
        return 'honeypot';
    }

    /**
     * @unreleased
     */
    public static function fromString(string $options = null): ValidationRule
    {
        return new self();
    }

    /**
    * @unreleased
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
