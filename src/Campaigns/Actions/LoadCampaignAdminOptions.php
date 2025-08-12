<?php

namespace Give\Campaigns\Actions;

use Give\API\REST\V3\Routes\Campaigns\ValueObjects\CampaignRoute;

/**
 * The purpose of this action is to have a centralized place for localizing options used on many different places
 * by campaign scripts (list tables, blocks, etc.)
 *
 * @since 4.6.1 Rename to LoadCampaignAdminOptions
 * @since 4.0.0
 */
class LoadCampaignAdminOptions
{
    public function __invoke()
    {
        wp_register_script('give-campaign-options', false);

        wp_localize_script('give-campaign-options', 'GiveCampaignOptions',
            [
                'isAdmin' => is_admin(),
                'adminUrl' => admin_url(),
                'apiRoot' => rest_url(CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS),
                'apiNonce' => wp_create_nonce('wp_rest'),
                'campaignsAdminUrl' => admin_url('edit.php?post_type=give_forms&page=give-campaigns'),
                'currency' => give_get_currency(),
                'currencySymbol' => give_currency_symbol(),
                'isRecurringEnabled' => defined('GIVE_RECURRING_VERSION')
                    ? GIVE_RECURRING_VERSION
                    : null,
                'admin' => is_admin()
                    ? [
                        'showCampaignInteractionNotice' => !get_user_meta(get_current_user_id(), 'givewp_show_campaign_interaction_notice', true),
                        'showFormGoalNotice' => !get_user_meta(get_current_user_id(), 'givewp_campaign_form_goal_notice', true),
                        'showExistingUserIntroNotice' => !get_user_meta(get_current_user_id(), 'givewp_campaign_existing_user_intro_notice', true) &&
                                                         version_compare((string)get_option('give_version_upgraded_from', '4.0.0'), '4.0.0', '<'),
                        'showCampaignListTableNotice' => !get_user_meta(get_current_user_id(), 'givewp_campaign_listtable_notice', true),
                        'showCampaignFormNotice' => !get_user_meta(get_current_user_id(), 'givewp_campaign_form_notice', true),
                        'showCampaignSettingsNotice' => !get_user_meta(get_current_user_id(), 'givewp_campaign_settings_notice', true)
                    ]
                    : null,
            ]
        );

        wp_enqueue_script('give-campaign-options');
    }
}
