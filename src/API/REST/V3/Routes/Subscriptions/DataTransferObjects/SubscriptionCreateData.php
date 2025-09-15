<?php

namespace Give\API\REST\V3\Routes\Subscriptions\DataTransferObjects;

use Exception;
use Give\API\REST\V3\Routes\Subscriptions\Fields\SubscriptionFields;
use Give\Subscriptions\Models\Subscription;
use WP_REST_Request;

/**
 * @since 4.8.0
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
     * @since 4.8.0
     *
     * @param WP_REST_Request $request
     * @return SubscriptionCreateData
     */
    public static function fromRequest(WP_REST_Request $request): SubscriptionCreateData
    {
        return new self($request->get_params());
    }



    /**
     * Convert to Subscription model
     *
     * @since 4.8.0
     *
     * @return Subscription
     * @throws Exception
     */
    public function createSubscription(): Subscription
    {
        // Filter out auto-generated fields
        $subscriptionAttributes = array_filter($this->attributes, function ($key) {
            return !in_array($key, ['id', 'createdAt', 'updatedAt'], true);
        }, ARRAY_FILTER_USE_KEY);

        return Subscription::create($subscriptionAttributes);
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
            if ($key === 'id' || $key === 'createdAt' || $key === 'updatedAt') {
                // Skip these fields as they are auto-generated
                continue;
            }

            $processedAttributes[$key] = SubscriptionFields::processValue($key, $value);
        }

        return $processedAttributes;
    }
}
