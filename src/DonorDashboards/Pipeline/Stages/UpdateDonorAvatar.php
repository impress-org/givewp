<?php

namespace Give\DonorDashboards\Pipeline\Stages;

/**
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
        $attributeMetaMap = [
            'avatarId' => '_give_donor_avatar_id',
        ];

        foreach ($attributeMetaMap as $attribute => $metaKey) {
            if (key_exists($attribute, $this->data)) {
                $this->donor->update_meta($metaKey, $this->data[$attribute]);
            }
        }
    }
}
