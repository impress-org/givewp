<?php

namespace Give\Tests\Unit\PaymentGateways;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\PaymentGateways\Exceptions\OverflowException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use PHPUnit\Framework\TestCase;

/**
 * @since 2.19.0
 */
class PaymentGatewaysRegisterTest extends TestCase
{
    /**
     * @var PaymentGatewayRegister
     */
    private $paymentGatewayRegister;

    protected function setUp()
    {
        $this->paymentGatewayRegister = new PaymentGatewayRegister();
    }

    /**
     * @since 2.19.0
     */
    public function testPaymentGatewayRegistererIsTraversable()
    {
        $this->assertInstanceOf(\Traversable::class, $this->paymentGatewayRegister);
    }

    /**
     * @since 2.19.0
     * @throws Exception
     * @throws OverflowException
     */
    public function testNewPaymentGatewayClassMustExtendPaymentGateway()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->paymentGatewayRegister->registerGateway(MockAuthorizeNet::class);
    }

    /**
     * @since 2.19.0
     */
    public function testRegisterGateways()
    {
        $this->paymentGatewayRegister->registerGateway(MockStripe::class);
        $this->paymentGatewayRegister->registerGateway(MockPayPal::class);
        $this->assertCount(2, $this->paymentGatewayRegister->getPaymentGateways());
    }

    /**
     * @since 2.19.0
     * @throws Exception
     * @throws OverflowException
     */
    public function testRegisterAlreadyRegisteredPaymentGateway()
    {
        $this->expectException(OverflowException::class);
        $this->paymentGatewayRegister->registerGateway(MockStripe::class);
        $this->paymentGatewayRegister->registerGateway(MockStripe::class);
    }

    /**
     * @since 2.19.0
     * @throws Exception
     * @throws OverflowException
     */
    public function testShouldGetRegisteredPaymentGateways()
    {
        $this->paymentGatewayRegister->registerGateway(MockStripe::class);
        $this->paymentGatewayRegister->registerGateway(MockPaypal::class);

        $gateways = $this->paymentGatewayRegister->getPaymentGateways();

        $this->assertEquals([
            MockStripe::id() => MockStripe::class,
            MockPaypal::id() => MockPaypal::class
        ], $gateways);
    }

    /**
     * @since 2.30.0
     * @throws Exception
     * @throws OverflowException
     */
    public function testShouldGetV2RegisteredPaymentGateways()
    {
        $this->paymentGatewayRegister->registerGateway(MockV2Gateway::class);

        $gateways = $this->paymentGatewayRegister->getPaymentGateways(2);

        $this->assertEquals([
            MockV2Gateway::id() => MockV2Gateway::class,
        ], $gateways);
    }
}

class MockV2Gateway extends PaymentGateway
{

    /**
     * @return string
     */
    public static function id(): string
    {
        return 'v2-gateway';
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return self::id();
    }

    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        return '';
    }

    public function getName(): string
    {
        return 'v2 Gateway';
    }

    public function getPaymentMethodLabel(): string
    {
        return 'Deprecated Gateway';
    }

    public function createPayment(Donation $donation, $gatewayData)
    {
        // TODO: Implement createPayment() method.
    }

    public function refundDonation(Donation $donation)
    {
        // TODO: Implement refundDonation() method.
    }
}

class MockAuthorizeNet
{
}

class MockStripe extends PaymentGateway
{
    /**
     * @inheritDoc
     */
    public static function isDeprecated(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public static function id(): string
    {
        return 'mock-stripe';
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return self::id();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'Stripe Payment Method';
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return 'Credit Card';
    }

    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        // TODO: Implement getLegacyFormFieldMarkup() method.
    }

    public function createPayment(Donation $donation, $gatewayData = null)
    {
        // TODO: Implement createPayment() method.
    }

    /**
     * @since 2.20.0
     *
     * @inerhitDoc
     */
    public function refundDonation(Donation $donation)
    {
    }
}

class MockPaypal extends PaymentGateway
{
    /**
     * @inheritDoc
     */
    public static function isDeprecated(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public static function id(): string
    {
        return 'mock-paypal';
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return self::id();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'PayPal Payment Method';
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return 'PayPal';
    }

    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        // TODO: Implement getLegacyFormFieldMarkup() method.
    }

    public function createPayment(Donation $donation, $gatewayData = null)
    {
        // TODO: Implement createPayment() method.
    }

    /**
     * @since 2.20.0
     *
     * @inerhitDoc
     */
    public function refundDonation(Donation $donation)
    {
    }
}
