<?php

namespace Give\Tests\Unit\DonationSpam\Akismet\Actions;

use Give\DonationSpam\Akismet\Actions\ValidateDonation;
use Give\DonationSpam\Akismet\API;
use Give\DonationSpam\EmailAddressWhiteList;
use Give\DonationSpam\Exceptions\SpamDonationException;
use Give\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @since 3.15.0
 */
final class ValidateDonationTest extends TestCase
{
    protected $spamResponse = [1 => 'true'];
    protected $notSpamResponse = [1 => 'false'];

    /**
     * @since 3.22.0 updated with new arguments
     * @since 3.15.0
     * @throws SpamDonationException
     */
    public function testValidatesNotSpamDonation(): void
    {
        $email = 'test@givewp.com';
        $comment = 'this is a comment';
        $firstName = 'test';

        /** @var API $akismet */
        $akismet = $this->mockAkismetAPI();
        $akismet->method('commentCheck')->willReturn($this->notSpamResponse);

        $action = new ValidateDonation(
            $akismet,
            new EmailAddressWhiteList()
        );

        $action($email, $comment, $firstName, '');

        $this->assertTrue(true); // Assert no exception thrown.
    }

    /**
     * @since 3.22.0 updated with new arguments
     * @since 3.15.0
     * @throws SpamDonationException
     */
    public function testThrowsSpamDonationException(): void
    {
        $email = 'test@givewp.com';
        $comment = 'this is a comment';
        $firstName = 'test';

        /** @var API|MockObject $akismet */
        $akismet = $this->mockAkismetAPI();
        $akismet->method('commentCheck')->willReturn($this->spamResponse);

        $action = new ValidateDonation(
            $akismet,
            new EmailAddressWhiteList()
        );

        $this->expectException(SpamDonationException::class);

        $action($email, $comment, $firstName, '');
    }

    /**
     * @since 3.15.0
     */
    protected function mockAkismetAPI()
    {
        return $this->createMockWithCallback(API::class, function(MockBuilder $mockBuilder) {
            $mockBuilder->setMethods(['commentCheck']);
            return $mockBuilder->getMock();
        });
    }
}
