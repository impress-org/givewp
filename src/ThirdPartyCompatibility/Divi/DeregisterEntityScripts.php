<?php

namespace Give\ThirdPartyCompatibility\Divi;

/**
 * @unreleased
 */
class DeregisterEntityScripts
{
    public function __invoke()
    {
        $entities = [
            'givewp-campaign-entity',
            'givewp-form-entity',
            'givewp-donor-entity',
        ];

        if (
            isset($_GET['page'])
            && $_GET['page'] == 'et_theme_builder'
        ) {
            foreach ($entities as $entity) {
                wp_dequeue_script($entity);
                wp_deregister_script($entity);
            }
        }
    }
}
