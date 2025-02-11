<?php

namespace Give\Tests\Unit\DonationSpam\Akismet\Actions;

use Give\DonationForms\DataTransferObjects\DonateControllerData;
use Give\DonationSpam\Akismet\Actions\ValidateDonation;
use Give\DonationSpam\Akismet\API;
use Give\DonationSpam\EmailAddressWhiteList;
use Give\DonationSpam\Exceptions\SpamDonationException;
use Give\Tests\TestCase;
use PHPUnit_Framework_MockObject_MockBuilder;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @since 3.15.0
 */
final class ValidateDonationTest extends TestCase
{
    protected $spamResponse = [1 => 'true'];
    protected $notSpamResponse = [1 => 'false'];

    /**
     * @since 3.15.0
     */
    public function testValidatesNotSpamDonation()
    {
        $data = new DonateControllerData();
        $data->email = 'test@givewp.com';
        $data->comment = 'this is a comment';
        $data->firstName = 'test';

        /** @var API|PHPUnit_Framework_MockObject_MockObject */
        $akismet = $this->mockAkismetAPI();
        $akismet->method('commentCheck')->willReturn($this->notSpamResponse);

        $action = new ValidateDonation(
            $akismet,
            new EmailAddressWhiteList()
        );

        $action->__invoke($data);

        $this->assertTrue(true); // Assert no exception thrown.
    }

    /**
     * @unreleased updated test to prefill data
     * @since 3.15.0
     * @throws SpamDonationException
     */
    public function testThrowsSpamDonationException()
    {
        $data = new DonateControllerData();
        $data->email = 'test@givewp.com';
        $data->comment = 'this is a comment';
        $data->firstName = 'test';

        /** @var API|PHPUnit_Framework_MockObject_MockObject $akismet */
        $akismet = $this->mockAkismetAPI();
        $akismet->method('commentCheck')->willReturn($this->spamResponse);

        $action = new ValidateDonation(
            $akismet,
            new EmailAddressWhiteList()
        );

        $this->expectException(SpamDonationException::class);

        $action($data);
    }

    /**
     * @since 3.15.0
     */
    protected function mockAkismetAPI()
    {
        return $this->createMock(API::class, function(PHPUnit_Framework_MockObject_MockBuilder $mockBuilder) {
            $mockBuilder->setMethods(['commentCheck']);
            return $mockBuilder->getMock();
        });
    }
}
