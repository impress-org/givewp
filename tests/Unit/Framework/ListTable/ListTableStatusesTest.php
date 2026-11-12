<?php

namespace Give\Tests\Unit\Framework\ListTable;

use Give\Campaigns\ListTable\CampaignsListTable;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\DonationForms\V2\ListTable\DonationFormsListTable;
use Give\DonationForms\V2\ValueObjects\DonationFormStatus;
use Give\Donations\ListTable\DonationsListTable;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\ListTable\DonorsListTable;
use Give\EventTickets\ListTable\EventTicketsListTable;
use Give\Subscriptions\ListTable\SubscriptionsListTable;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class ListTableStatusesTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testTablesWithoutAStatusSendOnlyIdAndColumns(): void
    {
        foreach ([DonorsListTable::class, EventTicketsListTable::class] as $table) {
            $this->assertSame(['id', 'columns'], array_keys(give($table)->toArray()), $table);
        }
    }

    /**
     * @since TBD
     */
    public function testStatusTablesSendTheVocabularyOfTheirEnum(): void
    {
        $this->assertSame(CampaignStatus::vocabulary()->toArray(), give(CampaignsListTable::class)->toArray()['statuses']);
        $this->assertSame(DonationStatus::vocabulary()->toArray(), give(DonationsListTable::class)->toArray()['statuses']);
        $this->assertSame(DonationFormStatus::vocabulary()->toArray(), give(DonationFormsListTable::class)->toArray()['statuses']);
        $this->assertSame(SubscriptionStatus::vocabulary()->toArray(), give(SubscriptionsListTable::class)->toArray()['statuses']);
    }

    /**
     * @since TBD
     */
    public function testIdAndColumnsComeFirst(): void
    {
        $this->assertSame(['id', 'columns', 'statuses'], array_keys(give(CampaignsListTable::class)->toArray()));
    }
}
