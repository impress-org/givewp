<?php

namespace Give\Tests\Unit\DonationForms\V2;

use Give\DonationForms\V2\DataTransferObjects\DonationFormQueryData;
use Give\DonationForms\V2\ValueObjects\DonationFormMetaKeys as LegacyDonationFormMetaKeys;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
final class DonationFormQueryDataFromObjectTest extends TestCase
{
    /**
     * When the attachMeta join did not return a row for the stat keys
     * (e.g. the stat is mirrored in wp_postmeta only, so the formmeta
     * join yields null), fromObject() must coerce the values to 0
     * rather than fataling on Money::fromDecimal(null, ...).
     *
     * @unreleased
     */
    public function testFromObjectCoercesNullStatValuesToZero()
    {
        $earningsKey = LegacyDonationFormMetaKeys::FORM_EARNINGS()->getKeyAsCamelCase();
        $salesKey = LegacyDonationFormMetaKeys::FORM_SALES()->getKeyAsCamelCase();

        $object = new \stdClass();
        $object->id = 1;
        $object->title = 'Test Form';
        $object->createdAt = '2026-01-01 00:00:00';
        $object->updatedAt = '2026-01-01 00:00:00';
        $object->status = 'publish';
        $object->settings = null;
        $object->{$earningsKey} = null;
        $object->{$salesKey} = null;
        $object->goalOption = null;
        $object->goalFormat = null;
        $object->recurringGoalFormat = null;
        $object->goalAmount = null;

        $dto = DonationFormQueryData::fromObject($object);

        $this->assertSame(0, $dto->totalNumberOfDonations);
        $this->assertSame(0, $dto->campaignId);
        $this->assertNotNull($dto->totalAmountDonated);
    }
}
