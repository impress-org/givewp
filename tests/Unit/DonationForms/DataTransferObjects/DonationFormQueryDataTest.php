<?php

namespace Give\Tests\Unit\DonationForms\DataTransferObjects;

use Give\DonationForms\DataTransferObjects\DonationFormQueryData;
use Give\DonationForms\ValueObjects\DonationFormMetaKeys;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

final class DonationFormQueryDataTest extends TestCase
{
    use RefreshDatabase;
    use LegacyDonationFormAdapter;

    /**
     * @dataProvider mockFormTypeProvider
     *
     * @unreleased
     */
    public function testToDonationFormShouldReturnDonationForm(string $mockFormType)
    {
        $mockForm = $mockFormType === 'multi' ? $this->createMultiLevelDonationForm() : $this->createSimpleDonationForm(
        );

        // simulate raw query to pass to DTO
        $query = DB::table('posts')
            ->select(
                ['ID', 'id'],
                ['post_title', 'title'],
                ['post_date', 'createdAt'],
                ['post_modified', 'updatedAt'],
                ['post_status', 'status']
            )
            ->attachMeta(
                'give_formmeta',
                'ID',
                'form_id',
                ...DonationFormMetaKeys::getColumnsForAttachMetaQuery()
            )
            ->where('post_type', 'give_forms')
            ->where('id', $mockForm->id)
            ->get();

        // create DTO from query object
        $donationFormQueryData = DonationFormQueryData::fromObject($query);

        // assert query data returns the donation form we expect.
        $this->assertEquals(
            $donationFormQueryData->toDonationForm(),
            $mockForm
        );
    }

    /**
     * @unreleased
     */
    public function testGetDonationFormLevelsMulti()
    {
        $dto = new DonationFormQueryData();

        $this->assertIsArray(
            $dto->getDonationFormLevels(
                json_decode('{"priceOption":"multi","donationLevels":[]}')
            )
        );
    }

    /**
     * @unreleased
     */
    public function testGetDonationFormLevelsSimple()
    {
        $dto = new DonationFormQueryData();

        $this->assertIsArray(
            $dto->getDonationFormLevels(
                json_decode('{"priceOption":"set", "setPrice":"100"}')
            )
        );
    }

    /**
     * @unreleased
     */
    public function testGetDonationFormLevelsNull()
    {
        $dto = new DonationFormQueryData();

        $this->assertIsArray(
            $dto->getDonationFormLevels(
                json_decode('{"priceOption":null}')
            )
        );
    }

    /**
     * @unreleased
     */
    public function mockFormTypeProvider(): array
    {
        return [
            ['multi'],
            ['set'],
        ];
    }
}
