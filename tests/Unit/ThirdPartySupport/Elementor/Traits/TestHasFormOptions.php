<?php

namespace Give\Tests\Unit\ThirdPartySupport\Elementor\Traits;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\ThirdPartySupport\Elementor\Traits\HasFormOptions;

/**
 * @since TBD
 * @covers \Give\ThirdPartySupport\Elementor\Traits\HasFormOptions
 */
class TestHasFormOptions extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testFormOptionsListStandaloneFormsInANoCampaignGroupAfterTheCampaigns(): void
    {
        $campaign = Campaign::factory()->create(['title' => 'Spring Appeal']);
        $campaignForm = DonationForm::factory()->create(['title' => 'Monthly giving']);
        give(CampaignRepository::class)->addCampaignForm($campaign, $campaignForm->id);
        $standaloneForm = DonationForm::factory()->create(['title' => 'General Fund']);

        $groups = $this->getFormOptionsWithCampaigns();

        $this->assertSame(['Spring Appeal', 'No campaign'], array_column($groups, 'label'));
        $this->assertSame([$campaign->defaultFormId, $campaignForm->id], array_keys($groups[0]['options']));
        $this->assertSame('General Fund', $groups[1]['options'][$standaloneForm->id] ?? null);
        $this->assertArrayNotHasKey($campaignForm->id, $groups[1]['options']);
    }

    /**
     * @since TBD
     */
    public function testFormOptionsListStandaloneFormsWhenNoFormBelongsToACampaign(): void
    {
        $form = DonationForm::factory()->create(['title' => 'General Fund']);

        $groups = $this->getFormOptionsWithCampaigns();

        $this->assertSame(['No campaign'], array_column($groups, 'label'));
        $this->assertSame('General Fund', $groups[0]['options'][$form->id] ?? null);
    }

    /**
     * @since TBD
     */
    public function testStandaloneFormsLeaveOutDraftsAndPeerToPeerForms(): void
    {
        $published = DonationForm::factory()->create(['title' => 'General Fund']);
        $draft = DonationForm::factory()->create(['title' => 'Draft fund', 'status' => DonationFormStatus::DRAFT()]);
        $peerToPeerForm = DonationForm::factory()->create(['title' => 'Team fundraiser']);
        $peerToPeer = Campaign::factory()->create();
        DB::table('give_campaigns')
            ->where('id', $peerToPeer->id)
            ->update(['campaign_type' => CampaignType::PEER_TO_PEER, 'form_id' => $peerToPeerForm->id]);

        $standaloneFormIds = array_map(function ($form) {
            return (int)$form->id;
        }, $this->getFormOptions()->getStandaloneForms());

        $this->assertContains($published->id, $standaloneFormIds);
        $this->assertNotContains($draft->id, $standaloneFormIds);
        $this->assertNotContains($peerToPeerForm->id, $standaloneFormIds);
    }

    /**
     * @since TBD
     */
    private function getFormOptionsWithCampaigns(): array
    {
        return $this->getFormOptions()->getFormOptionsWithCampaigns();
    }

    /**
     * @since TBD
     */
    private function getFormOptions()
    {
        return new class {
            use HasFormOptions;
        };
    }
}
