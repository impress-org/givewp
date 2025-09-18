<?php

namespace Give\API\REST\V3\Routes\Donations\DataTransferObjects;

use Exception;
use Give\API\REST\V3\Routes\Donations\Exceptions\DonationValidationException;
use Give\API\REST\V3\Routes\Donations\Fields\DonationFields;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationType;
use Give\Subscriptions\Models\Subscription;
use WP_REST_Request;

/**
 * @since 4.8.0
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
     * @var bool
     */
    private $updateRenewalDate;

    /**
     * @since 3.0.0
     */
    public function __construct(array $attributes)
    {
        // Extract updateRenewalDate before processing attributes
        $this->updateRenewalDate = $attributes['updateRenewalDate'] ?? false;

        $this->attributes = $this->processAttributes($attributes);
        $this->subscriptionId = $this->attributes['subscriptionId'] ?? 0;
        $this->type = $this->attributes['type'] ?? null;
        $this->isRenewal = $this->determineIfRenewal();
    }

    /**
     * Create DonationCreateData from REST request
     *
     * @since 4.8.0
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
     * @since 4.8.0
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
     * @since 4.8.0
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
     * @since 4.8.0
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
     * @since 4.8.0
     *
     * @return Donation
     * @throws Exception
     */
    public function createDonation(): Donation
    {
        $this->validateSubscriptionRules();
        $this->validateCreateDonation();

        // Filter out only the auto-generated id and campaignId fields
        $donationAttributes = array_filter($this->attributes, function ($key) {
            return !in_array($key, ['id', 'campaignId'], true);
        }, ARRAY_FILTER_USE_KEY);

        $donation = Donation::create($donationAttributes);

        return $donation;
    }

    /**
     * Convert to renewal donation using subscription
     *
     * @since 4.8.0
     *
     * @return Donation
     */
    public function createRenewal(): Donation
    {
        $this->validateSubscriptionRules();
        $this->validateCreateRenewal();

        $subscription = Subscription::find($this->subscriptionId);

        // Update subscription renewal date if requested BEFORE creating renewal
        // This ensures the bumpRenewalDate() calculation uses the correct base date
        if ($this->shouldUpdateRenewalDate()) {
            $this->updateSubscriptionRenewalDate($subscription);
        }

        // Pass the processed attributes to allow overriding values from the request
        // Filter out only the auto-generated id and campaignId fields and subscription-specific fields, allowing createdAt and updatedAt to be set
        $renewalAttributes = array_filter($this->attributes, function ($key) {
            return !in_array($key, ['id', 'campaignId','subscriptionId', 'type'], true);
        }, ARRAY_FILTER_USE_KEY);

        $donation = $subscription->createRenewal($renewalAttributes);

        return $donation;
    }

    /**
     * Update subscription renewal date with the createdAt date
     *
     * @since 4.8.0
     *
     * @param Subscription $subscription
     * @return void
     */
    private function updateSubscriptionRenewalDate(Subscription $subscription): void
    {
        if (isset($this->attributes['createdAt']) && $this->attributes['createdAt'] instanceof \DateTime) {
            $subscription->renewsAt = $this->attributes['createdAt'];
            $subscription->save();
        }
    }

    /**
     * Get the donation type
     *
     * @since 4.8.0
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
     * @since 4.8.0
     *
     * @return bool
     */
    public function isRenewal(): bool
    {
        return $this->isRenewal;
    }

    /**
     * Check if this is a subscription or renewal donation
     *
     * @since 4.8.0
     *
     * @return bool
     */
    public function isSubscriptionOrRenewal(): bool
    {
        return $this->type && in_array($this->type->getValue(), ['subscription', 'renewal'], true);
    }

    /**
     * Check if should update renewal date
     *
     * @since 4.8.0
     *
     * @return bool
     */
    public function shouldUpdateRenewalDate(): bool
    {
        return $this->updateRenewalDate && $this->isRenewal() && isset($this->attributes['createdAt']);
    }

    /**
     * Check if this is a single donation
     *
     * @since 4.8.0
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
     * @since 4.8.0
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
     * @since 4.8.0
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
     * @since 4.8.0
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
     * @since 4.8.0
     *
     * @param array $attributes
     * @return array
     */
    private function processAttributes(array $attributes): array
    {
        $processedAttributes = [];

        foreach ($attributes as $key => $value) {
            if ($key === 'id' || ! in_array($key, Donation::propertyKeys(), true)) {
                // Skip id field as it is always auto-generated or not valid for the Donation model
                continue;
            }

            $processedValue = DonationFields::processValue($key, $value);

            // Only include properties that are valid for the Donation model
            if ($processedValue !== null) {
                $processedAttributes[$key] = $processedValue;
            }
        }

        return $processedAttributes;
    }

    /**
     * Determine if this is a renewal donation
     *
     * @since 4.8.0
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
