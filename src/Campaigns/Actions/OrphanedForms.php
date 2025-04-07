<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\ValueObjects\CampaignType;
use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @unreleased
 */
class OrphanedForms
{
    private string $hookName = 'give_campaign_check_forms';
    private string $optionName = 'give_campaign_orphaned_forms';

    /**
     * @unreleased
     */
    public function registerAction()
    {
        if ( ! as_has_scheduled_action($this->hookName)) {
            as_schedule_recurring_action(
                time(),
                DAY_IN_SECONDS,
                $this->hookName
            );
        }

        add_action($this->hookName, function () {
            $this->runAction();
        });
    }

    /**
     * @unreleased
     */
    private function runAction()
    {
        // Bail out if we already collected orphaned forms
        if ( ! give_get_option($this->optionName)) {
            return;
        }

        $forms = DB::table('posts')
            ->select('ID', 'post_title')
            ->where('post_type', 'give_forms')
            ->whereNotIn('ID', function (QueryBuilder $builder) {
                $builder
                    ->from('give_campaign_forms')
                    ->select('form_id');
            })
            // p2p forms
            ->whereNotIn('ID', function (QueryBuilder $builder) {
                $builder
                    ->from('give_campaigns')
                    ->select('form_id')
                    ->where('campaign_type', CampaignType::CORE, '!=');
            })
            ->getAll(ARRAY_A);

        if ( ! $forms) {
            return;
        }

        give_update_option($this->optionName, $forms);
    }
}
