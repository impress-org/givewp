<?php

namespace Give\DonorDashboards\Pipeline\Stages;

/**
 * @since 2.10.0
 */
class UpdateDonorCompany implements Stage
{

    protected $data;
    protected $donor;

    public function __invoke($payload)
    {
        $this->data = $payload['data'];
        $this->donor = $payload['donor'];

        $this->updateCompanyInMetaDB();

        return $payload;
    }

    protected function updateCompanyInMetaDB()
    {
        $attributeMetaMap = [
            'company' => '_give_donor_company',
        ];

        foreach ($attributeMetaMap as $attribute => $metaKey) {
            if (key_exists($attribute, $this->data)) {
                $this->donor->update_meta($metaKey, $this->data[$attribute]);
            }
        }
    }
}
