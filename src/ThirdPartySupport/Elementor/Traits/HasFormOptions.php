<?php

namespace Give\ThirdPartySupport\Elementor\Traits;

use Exception;
use Give\Framework\Database\DB;

/**
 * Trait to get form options with campaigns
 *
 * @since 4.7.0
 */
trait HasFormOptions
{
    /**
     * Get form options with campaigns
     *
     * @since 4.7.0
     */
    public function getFormOptionsWithCampaigns(): array
    {
        $campaignsWithForms = $this->getCampaignsWithForms();

        if (empty($campaignsWithForms)) {
            return [];
        }

		$campaignOptions = [];
		$formOptionsGroup = [];
		$campaignGroups = [];

        // Group forms by campaign
        foreach ($campaignsWithForms as $item) {
            // Skip items without campaign association
            if (empty($item->campaign_id) || empty($item->campaign_title)) {
                continue;
            }

			$campaignId = $item->campaign_id;
			$campaignTitle = $item->campaign_title;

			// Add to campaign options if not already added
			if (!isset($campaignOptions[$campaignId])) {
				$campaignOptions[$campaignId] = $campaignTitle;
				$campaignGroups[$campaignId] = [
					'label' => $campaignTitle,
					'options' => [],
					'campaign_id' => $campaignId,
					'defaultFormId' => $item->default_form_id ?? null,
				];
			}

			// Add form to the campaign group
			$campaignGroups[$campaignId]['options'][$item->id] = $item->title;
        }

		// Ensure default form shows first in each campaign group
		foreach ($campaignGroups as $id => $group) {
			$defaultFormId = isset($group['defaultFormId']) ? (int)$group['defaultFormId'] : null;
			$defaultKey = $defaultFormId ?: null;
			if ($defaultKey !== null && isset($group['options'][$defaultKey])) {
				$orderedOptions = [];
				$orderedOptions[$defaultKey] = $group['options'][$defaultKey];
				foreach ($group['options'] as $formKey => $label) {
					if ((string)$formKey === (string)$defaultKey) {
						continue;
					}
					$orderedOptions[$formKey] = $label;
				}
				$campaignGroups[$id]['options'] = $orderedOptions;
			}
			// Remove helper key before returning
			unset($campaignGroups[$id]['defaultFormId']);
		}

		$formOptionsGroup = array_values($campaignGroups);

        return $formOptionsGroup;
    }

    /**
     * Get flattened form options from campaigns
     *
     * @since 4.7.0
     */
    public function getFormOptions(): array
    {
        $forms = $this->getForms();

        if (empty($forms)) {
            return [];
        }

        foreach ($forms as $form) {
            $options[$form->id] = $form->title;
        }

        return $options;
    }

    /**
     * Query campaigns with forms
     *
     * @since 4.7.0
     */
    public function getCampaignsWithForms(): array
    {
        try {
			$query = DB::table('posts', 'forms')
                ->select(
                    ['forms.ID', 'id'],
                    ['forms.post_title', 'title'],
					['campaigns.campaign_title', 'campaign_title'],
					['campaigns.id', 'campaign_id'],
					['campaigns.form_id', 'default_form_id']
                )
                ->innerJoin('give_campaign_forms', 'forms.ID', 'campaign_forms.form_id', 'campaign_forms')
				->innerJoin('give_campaigns', 'campaign_forms.campaign_id', 'campaigns.id', 'campaigns')
				->where('forms.post_status', 'publish')
				->where('forms.post_type', 'give_forms')
				->orderByRaw('CASE WHEN forms.ID = campaigns.form_id THEN 0 ELSE 1 END')
				->orderBy('forms.post_title', 'ASC');

            return $query->getAll();
        } catch (Exception $e) {
            error_log('getCampaignsWithForms error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get forms
     *
     * @since 4.7.0
     */
    public function getForms(): array
    {
        $forms = DB::table('posts')
            ->select(
                ['ID', 'id'],
                ['post_title', 'title']
            )
            ->where('post_type', 'give_forms')
            ->where('post_status', 'publish')
            ->orderBy('post_title')
            ->getAll();

        return $forms;
    }
}
