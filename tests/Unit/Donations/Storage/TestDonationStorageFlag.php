<?php

namespace Give\Tests\Unit\Donations\Storage;

use Give\Donations\Storage\DonationStorageFlag;
use Give\Donations\ValueObjects\DonationStorageMode;
use Give\Tests\TestCase;
use InvalidArgumentException;

/**
 * @since TBD
 */
class TestDonationStorageFlag extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        delete_option(DonationStorageFlag::OPTION);
    }

    /**
     * @since TBD
     */
    public function testDefaultsToPosts()
    {
        $this->assertTrue(DonationStorageFlag::get()->isPosts());
    }

    /**
     * @since TBD
     */
    public function testUnknownValueReadsAsPosts()
    {
        update_option(DonationStorageFlag::OPTION, 'bogus');

        $this->assertTrue(DonationStorageFlag::get()->isPosts());
    }

    /**
     * @since TBD
     */
    public function testCanRollBackFromTableToSyncing()
    {
        DonationStorageFlag::set(DonationStorageMode::SYNCING());
        DonationStorageFlag::set(DonationStorageMode::TABLE());
        $this->assertTrue(DonationStorageFlag::get()->isTable());

        DonationStorageFlag::set(DonationStorageMode::SYNCING());
        $this->assertTrue(DonationStorageFlag::get()->isSyncing());
    }

    /**
     * @since TBD
     */
    public function testCannotReturnToPosts()
    {
        DonationStorageFlag::set(DonationStorageMode::SYNCING());

        $this->expectException(InvalidArgumentException::class);
        DonationStorageFlag::set(DonationStorageMode::POSTS());
    }
}
