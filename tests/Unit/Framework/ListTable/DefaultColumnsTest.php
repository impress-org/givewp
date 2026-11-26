<?php

namespace Give\Tests\Unit\Framework\ListTable;

use Give\Campaigns\ListTable\CampaignsListTable;
use Give\DonationForms\V2\ListTable\DonationFormsListTable;
use Give\Donations\ListTable\DonationsListTable;
use Give\Framework\ListTable\ModelColumn;
use Give\Tests\TestCase;

/**
 * Locks the default order and visibility of the columns on the list screens. The client uses the
 * order of the schema as the default field order, and add-ons find columns by id.
 *
 * @since TBD
 */
class DefaultColumnsTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testFormsListOrdersAndShowsColumns(): void
    {
        $columns = give(DonationFormsListTable::class)->getColumnsArray();

        $this->assertSame(
            ['title', 'status', 'id', 'dateCreated', 'formCampaign', 'goal', 'donationCount', 'donorCount', 'donationRevenue'],
            array_column($columns, 'id')
        );
        $this->assertSame(['goal', 'donorCount'], $this->hiddenIds($columns));
    }

    /**
     * @since TBD
     */
    public function testCampaignsListShowsDonorsAfterDonations(): void
    {
        $columns = give(CampaignsListTable::class)->getColumnsArray();
        $ids = array_column($columns, 'id');

        $this->assertSame('donorsCount', $ids[array_search('donationsCount', $ids, true) + 1]);
        $this->assertNotContains('donorsCount', $this->hiddenIds($columns));
    }

    /**
     * @since TBD
     */
    public function testDonationsListOrdersAndShowsColumns(): void
    {
        $columns = give(DonationsListTable::class)->getColumnsArray();

        $this->assertSame(
            [
                'donor',
                'status',
                'id',
                'amount',
                'paymentType',
                'createdAt',
                'campaign',
                'form',
                'gateway',
                'subscriptionDonationType',
            ],
            array_column($columns, 'id')
        );
        $this->assertSame(['gateway', 'subscriptionDonationType'], $this->hiddenIds($columns));
    }

    /**
     * An add-on that adds a column after Status keeps working, and its column starts hidden.
     * addColumnAfter() does not set the visibility itself, so the add-on does it, as in
     * addColumn().
     *
     * @since TBD
     */
    public function testAddOnColumnAfterStatusFollowsStatus(): void
    {
        $listTable = give(DonationsListTable::class);
        try {
            $listTable->addColumnAfter('status', new class extends ModelColumn {
                public static function getId(): string
                {
                    return 'company';
                }

                public function getLabel(): string
                {
                    return 'Company';
                }

                public function getCellValue($model): string
                {
                    return '';
                }
            });
            $listTable->setColumnVisibility('company');

            $columns = $listTable->getColumnsArray();
        } finally {
            $listTable->removeColumn('company');
        }

        $ids = array_column($columns, 'id');

        $this->assertSame('company', $ids[array_search('status', $ids, true) + 1]);
        $this->assertContains('company', $this->hiddenIds($columns));
    }

    /**
     * @since TBD
     */
    private function hiddenIds(array $columns): array
    {
        return array_values(array_column(
            array_filter($columns, static function (array $column): bool {
                return !$column['visible'];
            }),
            'id'
        ));
    }
}
