<?php

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\PaymentGateways\Exceptions\OverflowException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use PHPUnit\Framework\TestCase;

/**
 * @unreleased
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
     * @unreleased
     */
    public function testPaymentGatewayRegistererIsTraversable()
    {
        $this->assertInstanceOf(\Traversable::class, $this->paymentGatewayRegister);
    }

    /**
     * @unreleased
     * @throws Exception
     * @throws OverflowException
     */
    public function testNewPaymentGatewayClassMustExtendPaymentGateway()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->paymentGatewayRegister->registerGateway(MockAuthorizeNet::class);
    }

    /**
     * @unreleased
     */
    public function testRegisterGateways()
    {
        $this->paymentGatewayRegister->registerGateway(MockStripe::class);
        $this->paymentGatewayRegister->registerGateway(MockPayPal::class);
        $this->assertCount(2, $this->paymentGatewayRegister->getPaymentGateways());
    }

    /**
     * @unreleased
     * @throws Exception
     * @throws OverflowException
     */
    public function testRegisterAlreadyRegisteredPaymentGateway()
    {
        $this->expectException(OverflowException::class);
        $this->paymentGatewayRegister->registerGateway(MockStripe::class);
        $this->paymentGatewayRegister->registerGateway(MockStripe::class);
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
    public static function id()
    {
        return 'stripe-credit-card-onsite';
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return self::id();
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'Stripe Payment Method';
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel()
    {
        return 'Credit Card';
    }

    public function getLegacyFormFieldMarkup($formId)
    {
        // TODO: Implement getLegacyFormFieldMarkup() method.
    }

    public function createPayment(GatewayPaymentData $paymentData)
    {
        // TODO: Implement createPayment() method.
    }
}

class MockPaypal extends PaymentGateway
{
    /**
     * @return string
     */
    public static function id()
    {
        return 'paypal-offsite';
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return self::id();
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'PayPal Payment Method';
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel()
    {
        return 'PayPal';
    }

    public function getLegacyFormFieldMarkup($formId)
    {
        // TODO: Implement getLegacyFormFieldMarkup() method.
    }

    public function createPayment(GatewayPaymentData $paymentData)
    {
        // TODO: Implement createPayment() method.
    }
}
