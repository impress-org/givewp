<?php

namespace Give\API\REST\V3\Routes\Subscriptions\Actions;

/**
 * @since 4.8.0
 */
class GetSubscriptionSharedParamsForGetMethods
{
    /**
     * @since 4.8.0
     */
    public function __invoke(): array
    {
        return [
            'includeSensitiveData' => [
                'description' => __('Include or not include data that can be used to contact or locate the donors, such as phone number, email, billing address, etc. (require proper permissions)',
                    'give'),
                'type' => 'boolean',
                'default' => false,
            ],
            'anonymousDonors' => [
                'description' => __('Exclude, include, or redact data that can be used to identify the donors, such as ID, first name, last name, etc (require proper permissions).',
                    'give'),
                'type' => 'string',
                'default' => 'exclude',
                'enum' => ['exclude', 'include', 'redact'],
            ],
        ];
    }
}
