<?php

namespace Give\Tests\Unit\DonationForms\V2\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\DonationForms\Repositories\DonationFormDataRepository;
use Give\DonationForms\V2\ListTable\Columns\CampaignColumn;
use Give\DonationForms\V2\ListTable\DonationFormsListTable;
use Give\Donations\ListTable\Columns\CampaignColumn as DonationCampaignColumn;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use TypeError;

/**
 * @since TBD
 */
class CampaignColumnTest extends TestCase
{
    use RefreshDatabase;
    use LegacyDonationFormAdapter;

    /**
     * @since TBD
     */
    public function testColumnIdDoesNotCollideWithTheDonationsCampaignColumn()
    {
        $this->assertSame('formCampaign', CampaignColumn::getId());
        $this->assertNotSame(DonationCampaignColumn::getId(), CampaignColumn::getId());
    }

    /**
     * @since TBD
     */
    public function testLinksToTheCampaignPageForACoreCampaign()
    {
        $campaign = Campaign::factory()->create(['title' => 'Spring Drive']);
        $form = $this->createSimpleDonationForm();
        give(CampaignRepository::class)->addCampaignForm($campaign, $form->id);

        $cell = $this->renderCell($form);

        $this->assertStringContainsString('Spring Drive', $cell);
        $this->assertStringContainsString("id={$campaign->id}", $cell);
    }

    /**
     * @since TBD
     */
    public function testRendersAnUnlinkedTitleForANonCoreCampaign()
    {
        $campaign = Campaign::factory()->create(['title' => 'Team Ride']);
        $form = $this->createSimpleDonationForm();
        give(CampaignRepository::class)->addCampaignForm($campaign, $form->id);

        DB::table('give_campaigns')
            ->where('id', $campaign->id)
            ->update(['campaign_type' => CampaignType::PEER_TO_PEER]);

        $cell = $this->renderCell($form);

        $this->assertSame('Team Ride', $cell);
        $this->assertStringNotContainsString('<a', $cell);
    }

    /**
     * @since TBD
     */
    public function testRendersNoCampaignForAStandaloneForm()
    {
        $this->assertSame(__('No campaign', 'give'), $this->renderCell($this->createSimpleDonationForm()));
    }

    /**
     * @since TBD
     */
    public function testATypeErrorRaisedByACellFilterDoesNotFailTheTable()
    {
        $throw = static function () {
            throw new TypeError('Argument #2 must be of type SomeOtherColumn');
        };

        add_filter('givewp_list_table_cell_value_formCampaign', $throw);

        try {
            $cell = $this->renderCell($this->createSimpleDonationForm());
        } finally {
            remove_filter('givewp_list_table_cell_value_formCampaign', $throw);
        }

        $this->assertStringContainsString('Something went wrong', $cell);
    }

    /**
     * @since TBD
     */
    private function renderCell($form): string
    {
        $listTable = new DonationFormsListTable();
        $listTable->setData(DonationFormDataRepository::forms([$form]));
        $listTable->items([$form], 'en-US');

        return $listTable->getItems()[0][CampaignColumn::getId()];
    }
}
