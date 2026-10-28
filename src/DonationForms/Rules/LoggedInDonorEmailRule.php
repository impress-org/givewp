<?php
/**
 * Logged-in donor email validation rule.
 *
 * @since TBD
 */

namespace Give\DonationForms\Rules;

use Closure;
use Give\Donors\Models\Donor;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

class LoggedInDonorEmailRule implements ValidationRule
{
    public static function id(): string
    {
        return 'logged_in_donor_email';
    }

    public static function fromString(?string $options = null): ValidationRule
    {
        return new self();
    }

    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        if (!is_user_logged_in()) {
            return;
        }

        $userId = get_current_user_id();
        $donor = Donor::whereUserId($userId);

        if ($donor && !empty($donor->email)) {
            $expectedEmail = strtolower($donor->email);
            if (strtolower((string) $value) !== $expectedEmail) {
                $fail(__('You are logged in and must use your account primary email address to submit a donation.', 'give'));
            }
            return;
        }

        $user = wp_get_current_user();
        if ($user && !empty($user->user_email)) {
            $expectedEmail = strtolower($user->user_email);
            if (strtolower((string) $value) !== $expectedEmail) {
                $fail(__('You are logged in and must use your account email address to submit a donation.', 'give'));
            }
        }
    }
}
