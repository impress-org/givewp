<?php

namespace Give\DonorDashboards\Tabs\EditProfileTab;

use Give\DonorDashboards\Helpers\SanitizeProfileData as SanitizeHelper;
use Give\DonorDashboards\Profile as Profile;
use Give\DonorDashboards\Tabs\Contracts\Route as RouteAbstract;
use WP_REST_Request;

/**
 * @since 2.10.0
 */
class ProfileRoute extends RouteAbstract
{

    /**
     * @inheritdoc
     */
    public function endpoint()
    {
        return 'profile';
    }

    /**
     * @inheritdoc
     */
    public function args()
    {
        return [
            'data' => [
                'type' => 'string',
                'required' => true,
                'sanitize_callback' => [$this, 'sanitizeData'],
            ],
            'id' => [
                'type' => 'int',
                'required' => true,
            ],
        ];
    }

    /**
     * Handles profile update, and returns updated profile array
     *
     * @since 2.10.0
     *
     * @param WP_REST_Request $request
     *
     * @return array
     *
     */
    public function handleRequest(WP_REST_Request $request)
    {
        return $this->updateProfile($request->get_param('data'), give()->donorDashboard->getId());
    }

    /**
     * Updates profile model, then gets newly stored profile data to return
     *
     * @since 2.10.0
     *
     * @param int    $id Profile id to update
     *
     * @param object $data Object representing profile data to update
     *
     * @return array
     *
     */
    protected function updateProfile($data, $id)
    {
        $profile = new Profile($id);
        $profile->update($data);

        return [
            'profile' => $profile->getProfileData(),
        ];
    }

    /**
     * Sanitize profile data object
     *
     * @since 2.10.0
     *
     * @param \WP_REST_Request $request
     * @param string           $param
     *
     * @param string           $value JSON encoded string representing profile data
     *
     * @return object
     *
     */
    public function sanitizeData($value, $request, $param)
    {
        $sanitizeHelper = '\Give\DonorDashboards\Helpers\SanitizeProfileData';
        $values = json_decode($value);

        return [
            'firstName' => sanitize_text_field($values->firstName),
            'lastName' => sanitize_text_field($values->lastName),
            'company' => sanitize_text_field($values->company),
            'additionalEmails' => SanitizeHelper::sanitizeAdditionalEmails($values->additionalEmails),
            'additionalAddresses' => SanitizeHelper::sanitizeAdditionalAddresses($values->additionalAddresses),
            'primaryEmail' => sanitize_email($values->primaryEmail),
            'primaryAddress' => SanitizeHelper::sanitizeAddress($values->primaryAddress),
            'titlePrefix' => sanitize_text_field($values->titlePrefix),
            'avatarId' => SanitizeHelper::sanitizeInt($values->avatarId),
            'isAnonymous' => intval($values->isAnonymous),
        ];
    }

    protected function sanitizeValue($value, $config)
    {
        if ( ! empty($value) && is_callable($config['sanitizeCallback'])) {
            return $config['sanitizeCallback']($value);
        } else {
            return $config['default'];
        }
    }
}
