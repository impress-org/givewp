<?php

namespace Give\Tracking\TrackingData;

use Give\Tracking\Contracts\TrackData;

/**
 * Class WebsiteData
 *
 * Represents the website data.
 *
 * @package Give\Tracking\TrackingData
 * @since 2.10.0
 */
class WebsiteData implements TrackData
{

    /**
     * Returns the collection data.
     *
     * @since 2.10.0
     *
     * @return array The collection data.
     */
    public function get()
    {
        global $wp_version;

        $data = [
            'site_title' => get_option('blogname'),
            'wp_version' => $wp_version,
            'givewp_version' => GIVE_VERSION,
            'home_url' => untrailingslashit(home_url()),
            'admin_url' => untrailingslashit(admin_url()),
            'is_multisite' => absint(is_multisite()),
            'site_language' => get_bloginfo('language'),
            'install_date' => $this->getPluginInstallDate(),
        ];

        return array_merge($data, (new ServerData())->get());
    }

    /**
     * Returns plugin install date
     *
     * @since 2.10.0
     * @return int
     */
    private function getPluginInstallDate()
    {
        $confirmationPageID = give_get_option('success_page');

        return strtotime(get_post_field('post_date', $confirmationPageID, 'db'));
    }
}

