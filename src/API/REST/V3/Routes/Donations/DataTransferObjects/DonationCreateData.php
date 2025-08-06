<?php

namespace Give\API\REST\V3\Routes\Donations\DataTransferObjects;

use DateTime;
use Exception;
use Give\API\REST\V3\Routes\Donations\Exceptions\DonationValidationException;
use Give\Donations\Models\Donation;
use Give\Donations\Properties\BillingAddress;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;
use WP_Error;
use WP_REST_Request;

/**
 * @unreleased
 */
class DonationCreateData
{
    /**
     * @var array
     */
    private $attributes;

    /**
     * @var bool
     */
    private $isRenewal;

    /**
     * @var DonationType|null
     */
    private $type;

    /**
     * @var int
     */
    private $subscriptionId;

    /**
     * @since 3.0.0
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $this->processAttributes($attributes);
        $this->subscriptionId = $this->attributes['subscriptionId'] ?? 0;
        $this->type = $this->attributes['type'] ?? null;
        $this->isRenewal = $this->determineIfRenewal();
    }

    /**
     * Create DonationCreateData from REST request
     *
     * @unreleased
     *
     * @param WP_REST_Request $request
     * @return DonationCreateData
     */
    public static function fromRequest(WP_REST_Request $request): DonationCreateData
    {
        return new self($request->get_params());
    }

    /**
     * Validate data for creating a single donation
     *
     * @unreleased
     *
     * @throws DonationValidationException
     */
    public function validateCreateDonation(): void
    {
        if ($this->isRenewal) {
            throw new DonationValidationException(
                __('Cannot create single donation for renewal type', 'give'),
                'invalid_donation_type',
                400
            );
        }

        $requiredFields = ['donorId', 'amount', 'gatewayId', 'mode', 'formId', 'firstName', 'email'];

        foreach ($requiredFields as $field) {
            if (!isset($this->attributes[$field])) {
                throw new DonationValidationException(
                    sprintf(__('Missing required field: %s', 'give'), $field),
                    'missing_required_field',
                    400
                );
            }
        }
    }

    /**
     * Validate data for creating a renewal donation
     *
     * @unreleased
     *
     * @throws DonationValidationException
     */
    public function validateCreateRenewal(): void
    {
        if (!$this->isRenewal) {
            throw new DonationValidationException(
                __('Cannot create renewal donation for non-renewal type', 'give'),
                'invalid_donation_type',
                400
            );
        }

        $requiredFields = ['subscriptionId', 'type'];

        foreach ($requiredFields as $field) {
            if (!isset($this->attributes[$field])) {
                throw new DonationValidationException(
                    sprintf(__('Missing required field: %s', 'give'), $field),
                    'missing_required_field',
                    400
                );
            }
        }

        // Validate subscription exists
        $subscription = Subscription::find($this->subscriptionId);
        if (!$subscription) {
            throw new DonationValidationException(
                __('Subscription not found', 'give'),
                'subscription_not_found',
                404
            );
        }

        // Ensure total donations don't exceed subscription installments
        if ($subscription->installments > 0 && $subscription->totalDonations() >= $subscription->installments) {
            throw new DonationValidationException(
                __('Cannot create donation: subscription installments limit reached', 'give'),
                'subscription_installments_exceeded',
                400
            );
        }
    }

    /**
     * Validate subscription-related rules
     *
     * @unreleased
     *
     * @throws DonationValidationException
     */
    public function validateSubscriptionRules(): void
    {
        // When subscriptionId is greater than zero, type must be "subscription" or "renewal"
        if ($this->subscriptionId > 0) {
            if (!$this->type || !in_array($this->type->getValue(), ['subscription', 'renewal'], true)) {
                throw new DonationValidationException(
                    __('When subscriptionId is provided, type must be "subscription" or "renewal"', 'give'),
                    'invalid_donation_type_for_subscription',
                    400
                );
            }

            // Validate subscription exists
            $subscription = Subscription::find($this->subscriptionId);
            if (!$subscription) {
                throw new DonationValidationException(
                    __('Subscription not found', 'give'),
                    'subscription_not_found',
                    404
                );
            }

            // When creating a donation associated with subscriptionId, ensure type is not "subscription"
            // if a donation of that type already exists for this subscription
            if ($this->type->getValue() === 'subscription' && $subscription->totalDonations() > 0) {
                throw new DonationValidationException(
                    __('A subscription donation already exists for this subscription', 'give'),
                    'subscription_donation_already_exists',
                    400
                );
            }

            // When creating a subscription or renewal donation, ensure gatewayId matches the subscription's gateway
            if (in_array($this->type->getValue(), ['subscription', 'renewal'], true)) {
                $donationGatewayId = $this->attributes['gatewayId'] ?? null;
                if ($donationGatewayId && $subscription->gatewayId && $donationGatewayId !== $subscription->gatewayId) {
                    throw new DonationValidationException(
                        __('Gateway ID must match the subscription gateway for subscription and renewal donations', 'give'),
                        'gateway_mismatch_for_subscription_donation',
                        400
                    );
                }
            }
        } else {
            // When subscriptionId is zero, type can only be "single" (if provided)
            if ($this->type && $this->type->getValue() !== 'single') {
                throw new DonationValidationException(
                    __('When subscriptionId is zero, type can only be "single"', 'give'),
                    'invalid_donation_type_for_single',
                    400
                );
            }

            // Set type to single if not provided
            if (!$this->type) {
                $this->attributes['type'] = DonationType::SINGLE();
            }
        }
    }

    /**
     * Convert to Donation model
     *
     * @unreleased
     *
     * @return Donation
     * @throws Exception
     */
    public function toDonation(): Donation
    {
        // Validate subscription rules first
        $this->validateSubscriptionRules();
        
        // Validate donation creation
        $this->validateCreateDonation();
        
        // Filter out auto-generated fields
        $donationAttributes = array_filter($this->attributes, function ($key) {
            return !in_array($key, ['id', 'createdAt', 'updatedAt'], true);
        }, ARRAY_FILTER_USE_KEY);

        return Donation::create($donationAttributes);
    }

    /**
     * Convert to renewal donation using subscription
     *
     * @unreleased
     *
     * @return Donation
     */
    public function toRenewal(): Donation
    {
        // Validate subscription rules first
        $this->validateSubscriptionRules();
        
        // Validate renewal creation
        $this->validateCreateRenewal();
        
        $subscription = Subscription::find($this->subscriptionId);
        
        // Pass the processed attributes to allow overriding values from the request
        // Filter out auto-generated fields and subscription-specific fields
        $renewalAttributes = array_filter($this->attributes, function ($key) {
            return !in_array($key, ['id', 'createdAt', 'updatedAt', 'subscriptionId', 'type'], true);
        }, ARRAY_FILTER_USE_KEY);

        return $subscription->createRenewal($renewalAttributes);
    }

    /**
     * Get the donation type
     *
     * @unreleased
     *
     * @return DonationType|null
     */
    public function getType(): ?DonationType
    {
        return $this->type;
    }

    /**
     * Check if this is a renewal donation
     *
     * @unreleased
     *
     * @return bool
     */
    public function isRenewal(): bool
    {
        return $this->isRenewal;
    }

    /**
     * Check if this is a single donation
     *
     * @unreleased
     *
     * @return bool
     */
    public function isSingle(): bool
    {
        return $this->type && $this->type->isSingle();
    }

    /**
     * Check if this is a subscription donation
     *
     * @unreleased
     *
     * @return bool
     */
    public function isSubscription(): bool
    {
        return $this->type && $this->type->isSubscription();
    }

    /**
     * Get the subscription ID
     *
     * @unreleased
     *
     * @return int
     */
    public function getSubscriptionId(): int
    {
        return $this->subscriptionId;
    }

    /**
     * Get the processed attributes
     *
     * @unreleased
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Process attributes for special data types
     *
     * @unreleased
     *
     * @param array $attributes
     * @return array
     */
    private function processAttributes(array $attributes): array
    {
        $processedAttributes = [];

        foreach ($attributes as $key => $value) {
            if ($key === 'id' || $key === 'createdAt' || $key === 'updatedAt') {
                // Skip these fields as they are auto-generated
                continue;
            }

            $processedAttributes[$key] = $this->processFieldValue($key, $value);
        }

        return $processedAttributes;
    }

    /**
     * Process field values for special data types
     *
     * @unreleased
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    private function processFieldValue(string $key, $value)
    {
        switch ($key) {
            case 'amount':
            case 'feeAmountRecovered':
                if (is_array($value)) {
                    // Handle Money object array format: ['amount' => 100.00, 'currency' => 'USD']
                    if (isset($value['amount']) && isset($value['currency'])) {
                        return Money::fromDecimal($value['amount'], $value['currency']);
                    }
                }
                return $value;

            case 'status':
                if (is_string($value) && DonationStatus::isValid($value)) {
                    return new DonationStatus($value);
                }
                return $value;

            case 'type':
                if (is_string($value) && DonationType::isValid($value)) {
                    return new DonationType($value);
                }
                return $value;

            case 'mode':
                if (is_string($value) && DonationMode::isValid($value)) {
                    return new DonationMode($value);
                }
                return $value;

            case 'billingAddress':
                if (is_array($value)) {
                    return BillingAddress::fromArray($value);
                }
                return $value;

            case 'createdAt':
                try {
                    if (is_string($value)) {
                        return new DateTime($value, wp_timezone());
                    } elseif (is_array($value)) {
                        return new DateTime($value['date'], new \DateTimeZone($value['timezone']));
                    }
                } catch (\Exception $e) {
                    throw new InvalidArgumentException("Invalid date format for {$key}: {$value}.");
                }
                return $value;

            default:
                return $value;
        }
    }

    /**
     * Determine if this is a renewal donation
     *
     * @unreleased
     *
     * @return bool
     */
    private function determineIfRenewal(): bool
    {
        return isset($this->attributes['subscriptionId']) &&
               $this->attributes['subscriptionId'] > 0 &&
               isset($this->attributes['type']) &&
               $this->attributes['type']->getValue() === 'renewal';
    }
} 