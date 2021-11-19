<?php

namespace Give\License;

/**
 * Class PremiumAddonManager
 * @package Give\License
 *
 * this class we use to manager premium addons list for licensing.
 *
 * @since 2.9.2
 */
class PremiumAddonsListManager
{
    /**
     * Products list api url.
     *
     * @since 2.9.2
     */
    const PRODUCTS_LIST_API_URL = 'https://givewp.com/edd-api/products';

    /**
     * Cached addon values for the same request, prevents subsequent transient queries
     *
     * @since 2.11.3
     */
    private $addonIds;

    /**
     * Get premium addons slugs as addons ids.
     *
     * @since 2.11.3 Always cache to avoid timeouts on givewp.com
     * @since 2.9.2
     *
     * @return array
     */
    private function getAddonsIds()
    {
        if (isset($this->addonIds)) {
            return $this->addonIds;
        }

        $optionName = 'give_premium_addons_ids';
        $cachedResult = get_transient($optionName);
        if ($cachedResult !== false) {
            return $this->addonIds = $cachedResult;
        }

        $response = wp_remote_get(
            self::PRODUCTS_LIST_API_URL . '?number=-1',
            [
                'timeout' => 3,
            ]
        );
        $productsInformation = wp_remote_retrieve_body($response);
        $responseCode = wp_remote_retrieve_response_code($response);
        if ( ! $productsInformation || $responseCode < 200 || $responseCode > 299) {
            set_transient($optionName, [], HOUR_IN_SECONDS);

            return $this->addonIds = [];
        }

        $productsInformation = json_decode($productsInformation, true);
        $productsIds = [];
        foreach ($productsInformation['products'] as $product) {
            $productsIds[] = $product['info']['slug'];
        }

        if ($productsIds) {
            set_transient($optionName, $productsIds, DAY_IN_SECONDS);
        }

        return $this->addonIds = $productsIds;
    }

    /**
     * Return whether or not addon is premium addon.
     *
     * @since 2.9.2
     *
     * @param string $pluginURI
     *
     * @return bool
     */
    public function isPremiumAddons($pluginURI)
    {
        $addonId = basename($pluginURI);
        $addonsIds = $this->getAddonsIds();

        return in_array($addonId, $addonsIds, true);
    }
}
