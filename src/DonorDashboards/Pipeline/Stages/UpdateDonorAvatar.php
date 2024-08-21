<?php

namespace Give\DonorDashboards\Pipeline\Stages;

use Give\Log\Log;
use WP_REST_Response;

/**
 * @since 3.14.2 added security measures to updateAvatarInMetaDB
 * @since 2.10.0
 */
class UpdateDonorAvatar implements Stage
{

    protected $data;
    protected $donor;

    public function __invoke($payload)
    {
        $this->data = $payload['data'];
        $this->donor = $payload['donor'];

        $this->updateAvatarInMetaDB();

        return $payload;
    }

    protected function updateAvatarInMetaDB()
    {
        if (!array_key_exists('avatarId', $this->data)) {
            return false;
        }

        $avatarId = $this->data['avatarId'];

        if (give()->donorDashboard->getAvatarId() && !give()->donorDashboard->avatarBelongsToCurrentUser($avatarId)) {
            Log::error(
                'Avatar update permission denied.',
                [
                    'donorId' => give()->donorDashboard->getId(),
                    'avatarId' => give()->donorDashboard->getAvatarId()
                ]
            );

            return new WP_REST_Response(
                [
                    'status' => 401,
                    'response' => 'unauthorized',
                    'body_response' => [
                        'message' => __('Avatar update permission denied.', 'give'),
                    ],
                ]
            );
        }

        return $this->donor->update_meta('_give_donor_avatar_id', $avatarId);
    }
}
