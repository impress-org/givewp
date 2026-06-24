<?php

namespace Give\Tests\Unit\DonationSpam\Akismet\Actions;

use Give\DonationSpam\Akismet\Actions\ValidateDonation;
use Give\DonationSpam\Akismet\Actions\ValidateDonationOnFinalSubmission;
use Give\Tests\TestCase;

/**
 * @since 4.16.0
 */
final class ValidateDonationOnFinalSubmissionTest extends TestCase
{
    /**
     * Per-step validations exist for field-level UX feedback only. Running the Akismet check on
     * every step makes Akismet treat the repeated requests from one IP as the start of a new
     * donation flood, so the check must be skipped for in-progress (non-final) validations.
     *
     * @since 4.16.0
     */
    public function testDoesNotRunAkismetCheckForInProgressValidation(): void
    {
        $validateDonation = $this->createMock(ValidateDonation::class);
        $validateDonation->expects($this->never())->method('__invoke');

        $action = $this->makeAction($validateDonation);

        $action([
            'email' => 'donor@givewp.com',
            'comment' => 'a comment',
            'firstName' => 'Bill',
            'lastName' => 'Murray',
        ], false);
    }

    /**
     * @since 4.16.0
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

        $action = $this->makeAction($validateDonation);

        $action($data, true);
    }

    /**
     * Guards the fail-safe default: any caller that fires the action without the flag (e.g. legacy
     * integrations) is treated as a final submission and is still checked for spam.
     *
     * @since 4.16.0
     */
    public function testDefaultsToFinalSubmissionWhenFlagOmitted(): void
    {
        $validateDonation = $this->createMock(ValidateDonation::class);
        $validateDonation->expects($this->once())->method('__invoke');

        $action = $this->makeAction($validateDonation);

        $action([
            'email' => 'donor@givewp.com',
            'comment' => '',
            'firstName' => 'Bill',
            'lastName' => 'Murray',
        ]);
    }

    /**
     * @since 4.16.0
     */
    public function testDoesNotRunAkismetCheckWhenNotEnabledAndConfigured(): void
    {
        $validateDonation = $this->createMock(ValidateDonation::class);
        $validateDonation->expects($this->never())->method('__invoke');

        $action = $this->makeAction($validateDonation, false);

        $action([
            'email' => 'donor@givewp.com',
            'comment' => 'a comment',
            'firstName' => 'Bill',
            'lastName' => 'Murray',
        ], true);
    }

    /**
     * Builds the action with a stubbed Akismet enabled/configured check, so the spam check can be
     * exercised without requiring the Akismet plugin/key to be configured in the test environment.
     *
     * @since 4.16.0
     */
    private function makeAction(ValidateDonation $validateDonation, bool $enabledAndConfigured = true): ValidateDonationOnFinalSubmission
    {
        $action = $this->getMockBuilder(ValidateDonationOnFinalSubmission::class)
            ->setConstructorArgs([$validateDonation])
            ->onlyMethods(['isAkismetEnabledAndConfigured'])
            ->getMock();
        $action->method('isAkismetEnabledAndConfigured')->willReturn($enabledAndConfigured);

        return $action;
    }
}
