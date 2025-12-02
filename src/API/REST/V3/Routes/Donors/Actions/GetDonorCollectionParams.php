<?php

namespace Give\API\REST\V3\Routes\Donors\Actions;

/**
 * @unreleased
 */
class GetDonorCollectionParams
{
    /**
     * @unreleased
     */
    public function __invoke(): array
    {
        return [
            'sort' => [
                'description' => __('The field by which to sort the donors.', 'give'),
                'type' => 'string',
                'default' => 'id',
                'enum' => [
                    'id',
                    'createdAt',
                    'name',
                    'firstName',
                    'lastName',
                    'totalAmountDonated',
                    'totalNumberOfDonations',
                ],
            ],
            'direction' => [
                'description' => __('The direction of sorting: ascending (ASC) or descending (DESC).', 'give'),
                'type' => 'string',
                'default' => 'DESC',
                'enum' => ['ASC', 'DESC'],
            ],
            'onlyWithDonations' => [
                'description' => __('Whether to include only donors who have made donations.', 'give'),
                'type' => 'boolean',
                'default' => true,
            ],
            'mode' => [
                'description' => __(
                    'The mode of donations to filter by "live" or "test" (it only gets applied when "onlyWithDonations" is set to true).',
                    'give'
                ),
                'type' => 'string',
                'default' => 'live',
                'enum' => ['live', 'test'],
            ],
            'campaignId' => [
                'description' => __(
                    'The ID of the campaign to filter donors by - zero or empty mean "all campaigns" (it only gets applied when "onlyWithDonations" is set to true).',
                    'give'
                ),
                'type' => 'integer',
                'default' => 0,
            ],
            'search' => [
                'description' => __('Search donors by name or email.', 'give'),
                'type' => 'string',
            ],
        ];
    }
}
