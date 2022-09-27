<?php

namespace TestsNextGen\Unit\DonationForm\Repositories;

use Exception;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\NextGen\DonationForm\Factories\DonationFormFactory;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\DonationForm\Repositories\DonationFormRepository;
use GiveTests\TestCase;
use GiveTests\TestTraits\RefreshDatabase;

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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
}
