<?php

namespace Give\Tests\Feature\Controllers;

use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\PaymentGateways\Stripe\Controllers\NewStripeAccountOnBoardingController;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class NewStripeAccountOnBoardingControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var NewStripeAccountOnBoardingController
     */
    private $newStripeAccountOnBoardingController;

    /**
     * @throws \ReflectionException
     */
    public function setUp()
    {
        parent::setUp();

        $this->newStripeAccountOnBoardingController = give(NewStripeAccountOnBoardingController::class);
        $this->canProcessRequestOnCurrentPageMethod = (new \ReflectionObject(
            $this->newStripeAccountOnBoardingController
        ))->getMethod('canProcessRequestOnCurrentPage');
        $this->canProcessRequestOnCurrentPageMethod->setAccessible(true);
    }

    public function testShouldReturnTrueForV2DonationFomEditPage(): void
    {
        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();
        $url = "wp-admin/post.php?post=$form->id&action=edit&give_tab=stripe_form_account_options";
        $result = $this->canProcessRequestOnCurrentPageMethod->invokeArgs(
            $this->newStripeAccountOnBoardingController,
            [$url]
        );
        $this->assertTrue($result);
    }

    public function testShouldReturnTrueGlobalStripeSettingPage(): void
    {
        $url = 'wp-admin/edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings';
        $result = $this->canProcessRequestOnCurrentPageMethod->invokeArgs(
            $this->newStripeAccountOnBoardingController,
            [$url]
        );
        $this->assertTrue($result);
    }

    public function testShouldReturnFalseOtherPage(): void
    {
        $url = 'wp-admin/edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal-settings';
        $result = $this->canProcessRequestOnCurrentPageMethod->invokeArgs(
            $this->newStripeAccountOnBoardingController,
            [$url]
        );
        $this->assertFalse($result);

        /** @var Donation $donation */
        $donation = Donation::factory()->create();
        $url = "wp-admin/post.php?post=$donation->id&action=edit&give_tab=stripe_form_account_options";
        $result = $this->canProcessRequestOnCurrentPageMethod->invokeArgs(
            $this->newStripeAccountOnBoardingController,
            [$url]
        );
        $this->assertFalse($result);
    }
}
