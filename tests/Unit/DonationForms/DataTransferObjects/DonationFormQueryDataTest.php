<?php

namespace Give\Tests\Unit\DonationForms\DataTransferObjects;

use Give\DonationForms\V2\DataTransferObjects\DonationFormQueryData;
use Give\DonationForms\V2\ValueObjects\DonationFormMetaKeys;
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
     * @since 2.25.0
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
     * @since 2.25.0
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
     * @since 2.25.0
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
     * @since 2.25.0
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
     * @since 2.25.0
     */
    public function testGetDonationFormLevelsShouldReturnEmptyArrayWhenUsingMultiAndNoLevels()
    {
        $dto = new DonationFormQueryData();

        $object = json_encode([
            DonationFormMetaKeys::PRICE_OPTION()->getKeyAsCamelCase() => 'multi',
            DonationFormMetaKeys::DONATION_LEVELS()->getKeyAsCamelCase() => null
        ]);

        $levels = $dto->getDonationFormLevels(json_decode($object));

        $this->assertSame([], $levels);
    }

    /**
     * @since 2.25.0
     */
    public function testGetDonationFormLevelsShouldReturnEmptyArrayWhenUsingSetAndNoAmount()
    {
        $dto = new DonationFormQueryData();

        $object = json_encode([
            DonationFormMetaKeys::PRICE_OPTION()->getKeyAsCamelCase() => 'set',
            DonationFormMetaKeys::SET_PRICE()->getKeyAsCamelCase() => null
        ]);

        $levels = $dto->getDonationFormLevels(json_decode($object));

        $this->assertSame([], $levels);
    }

    /**
     * @since 2.25.0
     */
    public function mockFormTypeProvider(): array
    {
        return [
            ['multi'],
            ['set'],
        ];
    }
}
