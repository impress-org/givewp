<?php

namespace Give\Tests\Unit\Framework\ListTable;

use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\DonationForms\V2\ValueObjects\DonationFormStatus;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class StatusVocabulariesTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testCampaignOffersActiveAndArchivedInThatOrder(): void
    {
        $offered = array_column(array_filter(CampaignStatus::vocabulary()->toArray(), function ($entry) {
            return $entry['filterable'];
        }), 'value');

        $this->assertSame(['active', 'archived'], array_values($offered));
    }

    /**
     * @since TBD
     */
    public function testCampaignShowsTheOtherStatusesAsBadgesOnly(): void
    {
        $this->assertSame(
            ['Inactive', 'Draft', 'Pending', 'Processing', 'Failed'],
            array_column(array_slice(CampaignStatus::vocabulary()->toArray(), 2), 'label')
        );
    }

    /**
     * @since TBD
     */
    public function testEveryCampaignStatusHasALabel(): void
    {
        foreach (CampaignStatus::values() as $status) {
            $this->assertNotSame('', $status->label());
        }
    }

    /**
     * @since TBD
     */
    public function testDonationFormOffersTodaysStatuses(): void
    {
        $this->assertSame(
            ['publish', 'pending', 'draft', 'trash', 'upgraded'],
            DonationFormStatus::vocabulary()->offeredValues()
        );
    }

    /**
     * @since TBD
     */
    public function testDonationAndSubscriptionOfferEveryLabel(): void
    {
        $this->assertSame(array_keys(DonationStatus::labels()), DonationStatus::vocabulary()->offeredValues());
        $this->assertSame(array_keys(SubscriptionStatus::labels()), SubscriptionStatus::vocabulary()->offeredValues());
    }
}
