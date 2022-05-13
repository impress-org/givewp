<?php

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\PaymentGateways\Exceptions\OverflowException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
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
}

class MockAuthorizeNet
{
}

class MockStripe extends PaymentGateway
{
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

    public function getLegacyFormFieldMarkup($formId, $args)
    {
        // TODO: Implement getLegacyFormFieldMarkup() method.
    }

    public function createPayment(GatewayPaymentData $paymentData)
    {
        // TODO: Implement createPayment() method.
    }

    /**
     * @unreleased
     * @inerhitDoc
     */
    public function refundDonation(Donation $donation)
    {
    }
}

class MockPaypal extends PaymentGateway
{
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

    public function getLegacyFormFieldMarkup($formId, $args)
    {
        // TODO: Implement getLegacyFormFieldMarkup() method.
    }

    public function createPayment(GatewayPaymentData $paymentData)
    {
        // TODO: Implement createPayment() method.
    }

    /**
     * @unreleased
     * @inerhitDoc
     */
    public function refundDonation(Donation $donation)
    {
    }
}
