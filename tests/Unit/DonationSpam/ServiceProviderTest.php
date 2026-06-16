<?php

namespace Give\Tests\Unit\DonationSpam;

use Give\DonationSpam\Akismet\Actions\ValidateDonation;
use Give\DonationSpam\EmailAddressWhiteList;
use Give\DonationSpam\ServiceProvider;
use Give\Tests\TestCase;

/**
 * @since 3.16.0
 */
final class ServiceProviderTest extends TestCase
{
    /**
     * @since 3.16.0
     */
    public function testFilteredWhitelistIsArray()
    {
        add_filter('give_akismet_whitelist_emails', '__return_empty_string');

        give(EmailAddressWhiteList::class)
            ->validate('name@email.test');

        $this->assertTrue(true);
    }

    /**
     * Per-step validations exist for field-level UX feedback only. Running the Akismet check on
     * every step makes Akismet treat the repeated requests from one IP as the start of a new
     * donation flood, so the check must be skipped for in-progress (non-final) validations.
     *
     * @since TBD
     */
    public function testDoesNotRunAkismetCheckForInProgressValidation(): void
    {
        $validateDonation = $this->createMock(ValidateDonation::class);
        $validateDonation->expects($this->never())->method('__invoke');

        give()->singleton(ValidateDonation::class, static function () use ($validateDonation) {
            return $validateDonation;
        });

        $this->bootAkismetServiceProvider();

        do_action('givewp_donation_form_fields_validated', [
            'email' => 'donor@givewp.com',
            'comment' => 'a comment',
            'firstName' => 'Bill',
            'lastName' => 'Murray',
        ], false);
    }

    /**
     * @since TBD
     */
    public function testRunsAkismetCheckForFinalSubmission(): void
    {
        $data = [
            'email' => 'donor@givewp.com',
            'comment' => 'a comment',
            'firstName' => 'Bill',
            'lastName' => 'Murray',
        ];

        $validateDonation = $this->createMock(ValidateDonation::class);
        $validateDonation->expects($this->once())
            ->method('__invoke')
            ->with($data['email'], $data['comment'], $data['firstName'], $data['lastName']);

        give()->singleton(ValidateDonation::class, static function () use ($validateDonation) {
            return $validateDonation;
        });

        $this->bootAkismetServiceProvider();

        do_action('givewp_donation_form_fields_validated', $data, true);
    }

    /**
     * Guards the fail-safe default: any caller that fires the action without the flag (e.g. legacy
     * integrations) is treated as a final submission and is still checked for spam.
     *
     * @since TBD
     */
    public function testDefaultsToFinalSubmissionWhenFlagOmitted(): void
    {
        $validateDonation = $this->createMock(ValidateDonation::class);
        $validateDonation->expects($this->once())->method('__invoke');

        give()->singleton(ValidateDonation::class, static function () use ($validateDonation) {
            return $validateDonation;
        });

        $this->bootAkismetServiceProvider();

        do_action('givewp_donation_form_fields_validated', [
            'email' => 'donor@givewp.com',
            'comment' => '',
            'firstName' => 'Bill',
            'lastName' => 'Murray',
        ]);
    }

    /**
     * Boots the spam ServiceProvider with Akismet forced on, so the
     * givewp_donation_form_fields_validated listener is registered without requiring the Akismet
     * plugin/key to be configured in the test environment.
     *
     * @since TBD
     */
    private function bootAkismetServiceProvider(): void
    {
        $provider = $this->getMockBuilder(ServiceProvider::class)
            ->onlyMethods(['isAkismetEnabledAndConfigured'])
            ->getMock();
        $provider->method('isAkismetEnabledAndConfigured')->willReturn(true);

        $provider->boot();
    }

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();
        remove_all_actions('givewp_donation_form_fields_validated');
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        remove_all_actions('givewp_donation_form_fields_validated');
        parent::tearDown();
    }
}
