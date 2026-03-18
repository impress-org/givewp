<?php

namespace Give\VendorOverrides\Uplink\Actions;

use Give\License\Repositories\LicenseRepository;

class RegisterProduct
{
    /**
     * Register the product with the Uplink product registry.
     *
     * @unreleased
     */
    public function __invoke(array $products): array
    {
        $licenseRepository = give(LicenseRepository::class);

        $products[] = [
            'product'      => 'give',
            'slug'         => 'give',
            'embedded_key' => $licenseRepository->getBundledLicense(),
            'name'         => 'GiveWP',
            'version'      => GIVE_VERSION,
        ];

        return $products;
    }
}
