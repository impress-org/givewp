<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Models\CampaignPage;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\ValueObjects\CampaignPageStatus;
use Give_Form_Duplicator;

class DuplicateCampaign
{
    /**
     * Duplicate a campaign
     *
     * @unreleased
     */
    public function __invoke(Campaign $campaign): Campaign
    {
        require_once(GIVE_PLUGIN_DIR . '/includes/admin/forms/class-give-form-duplicator.php');

        $forms = $campaign->forms();
        $campaignRepository = give(CampaignRepository::class);

        $campaign->id = null;
        $campaign->title = sprintf(__('%s (copy)', 'give'), $campaign->title);
        $campaign->save();

        foreach ($forms->getAll() as $form) {
            if (! $post = get_post($form->id)) {
                continue;
            }

            $isDefaultForm = $campaign->defaultFormId === $form->id;

            $newFormId = wp_insert_post([
                'comment_status' => $post->comment_status,
                'ping_status' => $post->ping_status,
                'post_author' => get_current_user_id(),
                'post_content' => $post->post_content,
                'post_date_gmt' => current_time('mysql', true),
                'post_excerpt' => $post->post_excerpt,
                'post_name' => $post->post_name,
                'post_parent' => $post->post_parent,
                'post_password' => $post->post_password,
                'post_status' => $isDefaultForm ? 'publish' : 'draft',
                'post_title' => $post->post_title,
                'post_type' => $post->post_type,
                'to_ping' => $post->to_ping,
                'menu_order' => $post->menu_order,
            ]);

            Give_Form_Duplicator::duplicate_taxonomies($newFormId, $post);
            Give_Form_Duplicator::duplicate_meta_data($newFormId, $post);
            Give_Form_Duplicator::reset_stats($newFormId);

            if ($isDefaultForm) {
                $campaign->defaultFormId = $newFormId;
            }

            $campaignRepository->addCampaignForm($campaign, $newFormId, $isDefaultForm);
        }

        if ($campaignPage = CampaignPage::find($campaign->pageId)) {
            $campaignPage->id = null;
            $campaignPage->status = CampaignPageStatus::DRAFT();
            $campaignPage->campaignId = $campaign->id;

            // update campaign id attribute
            $campaignPage->content = preg_replace(
                '/"campaignId":(\d+)/',
                '"campaignId":' . $campaign->id,
                $campaignPage->content
            );

            $campaignPage->save();

            $campaign->pageId = $campaignPage->id;
        }

        $campaign->save();

        return $campaign;
    }
}
