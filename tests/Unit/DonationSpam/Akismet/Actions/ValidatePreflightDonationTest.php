<?php

namespace Give\Tests\Unit\DonationSpam\Akismet\Actions;

use Give\DonationSpam\Akismet\Actions\ValidatePreflightDonation;
use Give\DonationSpam\Akismet\API;
use Give\DonationSpam\EmailAddressWhiteList;
use Give\DonationSpam\Exceptions\SpamDonationException;
use Give\Tests\TestCase;
use PHPUnit_Framework_MockObject_MockBuilder;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @unreleased
 */
final class ValidatePreflightDonationTest extends TestCase
{
    protected $spamResponse = [1 => 'true'];
    protected $notSpamResponse = [1 => 'false'];

    /**
     * @unreleased
     * @throws SpamDonationException
     */
    public function testValidatesNotSpamDonation(): void
    {
        $data = [
            'comment' => 'This is a comment',
            'email' => 'billmurray@givewp.com',
            'firstName' => 'Bill',
            'lastName' => '',
        ];

        /** @var API|PHPUnit_Framework_MockObject_MockObject $akismet */
        $akismet = $this->mockAkismetAPI();
        $akismet->method('commentCheck')->willReturn($this->notSpamResponse);

        $action = new ValidatePreflightDonation(
            $akismet,
            new EmailAddressWhiteList()
        );

        $action($data);

        $this->assertTrue(true);
    }

    /**
     * @unreleased
     * @throws SpamDonationException
     */
    public function testThrowsSpamDonationException(): void
    {
        $data = [
            'comment' => 'This is a comment',
            'email' => 'billmurray@givewp.com',
            'firstName' => 'Bill',
            'lastName' => '',
        ];

        /** @var API|PHPUnit_Framework_MockObject_MockObject $akismet */
        $akismet = $this->mockAkismetAPI();
        $akismet->method('commentCheck')->willReturn($this->spamResponse);

        $action = new ValidatePreflightDonation(
            $akismet,
            new EmailAddressWhiteList()
        );

        $this->expectException(SpamDonationException::class);

        $action($data);
    }

    /**
     * @unreleased
     */
    protected function mockAkismetAPI()
    {
        return $this->createMock(API::class, function(PHPUnit_Framework_MockObject_MockBuilder $mockBuilder) {
            $mockBuilder->setMethods(['commentCheck']);
            return $mockBuilder->getMock();
        });
    }
}
