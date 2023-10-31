<?php

namespace Give\Tests\Unit\DonationForms\Repositories;

use Closure;
use Exception;
use Give\DonationForms\Factories\DonationFormFactory;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Repositories\DonationFormRepository;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\FieldsAPI\DonationForm as Form;
use Give\Framework\FieldsAPI\Hidden;
use Give\Framework\FieldsAPI\Section;
use Give\Framework\FieldsAPI\Text;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

final class TestDonationFormRepository extends TestCase
{
    use RefreshDatabase;

    /**
     * @var DonationFormFactory
     */
    public $modelFactory;

    /**
     * @var DonationFormRepository
     */
    public $repository;

    public function setUp()
    {
        parent::setUp();

        $this->modelFactory = DonationForm::factory();
        $this->repository = give(DonationFormRepository::class);
    }

    /**
     * @since 3.0.0
     *
     * @return void
     *
     * @throws Exception
     */
    public function testGetByIdShouldReturnDonationForm()
    {
        $donationForm = $this->modelFactory->create();

        $donationFormFromDatabase = $this->repository->getById($donationForm->id);

        $this->assertInstanceOf(DonationForm::class, $donationFormFromDatabase);
        $this->assertEquals($donationForm->id, $donationFormFromDatabase->id);
    }

    /**
     * @since 3.0.0
     *
     * @return void
     *
     * @throws Exception
     */
    public function testInsertShouldAddDonationFormToDatabase()
    {
        $donationForm = $this->modelFactory->make();

        $this->repository->insert($donationForm);

        /** @var DonationForm $donationFormFromDatabase */
        $donationFormFromDatabase = $this->repository->getById($donationForm->id);

        $this->assertEquals($donationForm->getAttributes(), $donationFormFromDatabase->getAttributes());
    }

    /**
     * @since 3.0.0
     *
     * @return void
     *
     * @throws Exception
     */
    public function testInsertShouldFailValidationWhenMissingKeyAndThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $donationForm = $this->modelFactory->make([
            'title' => null,
        ]);

        $this->repository->insert($donationForm);
    }

    /**
     * @since 3.0.0
     *
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateShouldFailValidationAndThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $donationForm = $this->modelFactory->make([
            'title' => null,
        ]);

        $this->repository->update($donationForm);
    }

    /**
     * @since 3.0.0
     *
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateShouldUpdateDonationFormValuesInTheDatabase()
    {
        $donationForm = $this->modelFactory->create();
        $donationForm->title = 'Updated Form Title';

        $this->repository->update($donationForm);

        /** @var DonationForm $donationFormFromDatabase */
        $donationFormFromDatabase = $this->repository->getById($donationForm->id);

        $this->assertEquals('Updated Form Title', $donationFormFromDatabase->title);
    }

    /**
     * @since 3.0.0
     *
     * @return void
     *
     * @throws Exception
     */
    public function testDeleteShouldRemoveDonationFormFromTheDatabase()
    {
        $donationForm = $this->modelFactory->create();

        $this->repository->delete($donationForm);

        $form = $this->repository->getById($donationForm->id);
        $formMeta = DB::table('give_formmeta')
            ->where('form_id', $donationForm->id)
            ->getAll();

        $this->assertNull($form);
        $this->assertEmpty($formMeta);
    }

    /**
     *
     * @since 3.0.0
     */
    public function testShouldGetTotalNumberOfDonors()
    {
        $donationForm = DonationForm::factory()->create();
        Donation::factory()->count(3)->create(['formId' => $donationForm->id]);

        $total = $this->repository->getTotalNumberOfDonors($donationForm->id);

        $this->assertSame(3, $total);
    }

    /**
     *
     * @since 3.0.0
     */
    public function testShouldGetTotalNumberOfDonations()
    {
        $donationForm = DonationForm::factory()->create();
        Donation::factory()->count(3)->create(['formId' => $donationForm->id]);

        $total = $this->repository->getTotalNumberOfDonations($donationForm->id);

        $this->assertSame(3, $total);
    }

    /**
     * TODO: Notice in this test how we need to simulate a status update on the donation to trigger the legacy listener that update '_give_form_earnings' meta.  This should be resolved up the chain in our donation repository and/or factory so we don't have to do this.
     *
     * @since 3.0.0
     */
    public function testShouldGetTotalRevenue()
    {
        $donationForm = DonationForm::factory()->create();

        /** @var Donation[] $donations */
        $donations = Donation::factory()->count(3)->create([
                'formId' => $donationForm->id,
                'amount' => Money::fromDecimal('50.00', 'USD'),
                'status' => DonationStatus::PENDING()
            ]
        );

        // simulating a donation status change from pending to complete will trigger the form to update it's '_give_form_earnings' meta
        foreach ($donations as $donation) {
            $donation->status = DonationStatus::COMPLETE();
            $donation->save();
        }

        $total = $this->repository->getTotalRevenue($donationForm->id);

        $this->assertSame(150, $total);
    }

    /**
     * @since 3.0.0
     */
    public function testShouldGetFormDataGateways()
    {
        return $this->markTestIncomplete();
    }

    /**
     * @since 3.0.0
     */
    public function testIsLegacyFormShouldReturnTrueWhenFormIsLegacy()
    {
        $legacyForm = DB::table('posts')->insert([
            'post_title' => 'Legacy Form',
            'post_type' => 'give_forms',
            'post_status' => 'publish',
        ]);

        $legacyFormId = DB::last_insert_id();

        $this->assertTrue($this->repository->isLegacyForm($legacyFormId));
    }

    /**
     * @since 3.0.0
     */
    public function testIsLegacyFormShouldReturnFalseIfNotLegacy()
    {
        // create a new form
        $form = DonationForm::factory()->create();

        $this->assertFalse($this->repository->isLegacyForm($form->id));
    }


    /**
     * @since 3.0.0
     *
     * @return void
     * @throws Exception
     */
    public function testGetFormSchemaFromBlocksShouldReturnFormSchema()
    {
        $block = BlockModel::make([
            'clientId' => '8371d4c7-0e8d-4aff-a1a1-b4520f008132',
            'name' => 'givewp/section',
            'isValid' => true,
            'attributes' => [
                'title' => 'custom section title',
                'description' => 'custom section description',
            ],
            'innerBlocks' => [
                [
                    'clientId' => 'bddaa0ea-29bf-4143-b62d-aae3396e9b0f',
                    'name' => 'givewp/text',
                    'isValid' => true,
                    'attributes' => [
                        'fieldName' => 'givewp-custom-field-name',
                        'label' => 'GiveWP Custom Block',
                        'description' => 'GiveWP Custom Block description',
                    ],
                ],
            ],
        ]);

        $blockIndex = 1;
        $formId = 1;

        $blocks = BlockCollection::make([$block]);

        /** @var Form $formSchema */
        $formSchema = $this->repository->getFormSchemaFromBlocks($formId, $blocks);

        $form = new Form('donation-form');
        $form->defaultCurrency('USD');
        $form->append(
            Section::make('section-' . $blockIndex)
                ->label('custom section title')
                ->description('custom section description')
                ->append(
                    Text::make('givewp-custom-field-name')
                        ->label('GiveWP Custom Block')
                        ->description('GiveWP Custom Block description')
                        ->storeAsDonorMeta(false),
                    Hidden::make('formId')
                        ->defaultValue($formId)
                        ->rules(
                            'required', 'integer',
                            function ($value, Closure $fail, string $key, array $values) use ($formId) {
                                if ($value !== $formId) {
                                    $fail('Invalid donation form ID');
                                }
                            }
                        )
                )
        );

        $this->assertEquals($formSchema, $form);
    }

    /**
     * @since 3.0.0
     */
    public function testShouldGetTotalNumberOfSubscriptions(): void
    {
        $form = DonationForm::factory()->create();

        Subscription::factory()->count(2)->createWithDonation([
            'donationFormId' => $form->id,
        ]);

        $this->assertSame(2, $this->repository->getTotalNumberOfSubscriptions($form->id));
    }

    /**
     * @since 3.0.0
     */
    public function testShouldGetTotalNumberOfDonorsFromSubscriptions(): void
    {
        $donor1 = Donor::factory()->create();
        $donor2 = Donor::factory()->create();
        $form = DonationForm::factory()->create();

        $subscription1 = Subscription::factory()->createWithDonation([
            'donationFormId' => $form->id,
            'donorId' => $donor1->id,
        ]);

        $subscription2 = Subscription::factory()->createWithDonation([
            'donationFormId' => $form->id,
            'donorId' => $donor2->id,
        ]);

        $subscription3WithDonor2 = Subscription::factory()->createWithDonation([
            'donationFormId' => $form->id,
            'donorId' => $donor2->id,
        ]);

        $this->assertSame(2, $this->repository->getTotalNumberOfDonorsFromSubscriptions($form->id));
    }

    /**
     * @since 3.0.0
     */
    public function testShouldGetTotalRevenueFromSubscriptions(): void
    {
        $form = DonationForm::factory()->create();

        $subscription1 = Subscription::factory()->createWithDonation([
            'donationFormId' => $form->id,
        ]);

        $subscription2 = Subscription::factory()->createWithDonation([
            'donationFormId' => $form->id,
        ]);

        $amount = $subscription1->amount->add($subscription2->amount);

        $this->assertEquals($amount->formatToDecimal(), $this->repository->getTotalInitialAmountFromSubscriptions($form->id));
    }
}
