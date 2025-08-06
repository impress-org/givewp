<?php

namespace Give\API\REST\V3\Routes\Subscriptions\DataTransferObjects;

use Exception;
use Give\API\REST\V3\Routes\Subscriptions\Exceptions\SubscriptionValidationException;
use Give\API\REST\V3\Routes\Subscriptions\Helpers\SubscriptionFields;
use Give\Subscriptions\Models\Subscription;
use WP_REST_Request;

/**
 * @unreleased
 */
class SubscriptionCreateData
{
    /**
     * @var array
     */
    private $attributes;

    /**
     * @since 3.0.0
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $this->processAttributes($attributes);
    }

    /**
     * Create SubscriptionCreateData from REST request
     *
     * @unreleased
     *
     * @param WP_REST_Request $request
     * @return SubscriptionCreateData
     */
    public static function fromRequest(WP_REST_Request $request): SubscriptionCreateData
    {
        return new self($request->get_params());
    }

    /**
     * Validate data for creating a subscription
     *
     * @unreleased
     *
     * @throws SubscriptionValidationException
     */
    public function validateCreateSubscription(): void
    {
        $requiredFields = [
            'donorId',
            'donationFormId',
            'amount',
            'status',
            'period',
            'frequency',
            'gatewayId',
        ];

        foreach ($requiredFields as $field) {
            if (!isset($this->attributes[$field]) || empty($this->attributes[$field])) {
                throw new SubscriptionValidationException(
                    sprintf(__('Field "%s" is required', 'give'), $field),
                    'missing_required_field',
                    400
                );
            }
        }
    }

    /**
     * Convert to Subscription model
     *
     * @unreleased
     *
     * @return Subscription
     * @throws Exception
     */
    public function toSubscription(): Subscription
    {
        // Validate subscription creation
        $this->validateCreateSubscription();

        // Filter out auto-generated fields
        $subscriptionAttributes = array_filter($this->attributes, function ($key) {
            return !in_array($key, ['id', 'createdAt', 'updatedAt'], true);
        }, ARRAY_FILTER_USE_KEY);

        return Subscription::create($subscriptionAttributes);
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

            $processedAttributes[$key] = SubscriptionFields::processValue($key, $value);
        }

        return $processedAttributes;
    }
} 