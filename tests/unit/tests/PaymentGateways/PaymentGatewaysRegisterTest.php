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
        $this->paymentGatewayRegister = give(PaymentGatewayRegister::class);
        $this->resetGateways();
    }

    protected function tearDown()
    {
        $this->resetGateways();
    }

    public function resetGateways()
    {
        $gateways = $this->paymentGatewayRegister->getPaymentGateways();
        foreach ($gateways as $gatewayClass) {
            /** @var PaymentGateway $gateway */
            $gateway = give($gatewayClass);
            $this->paymentGatewayRegister->unregisterGateway($gateway->getId());
        }
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
        $this->paymentGatewayRegister->registerGateway(AuthorizeNet::class);
    }

    /**
     * @unreleased
     */
    public function testRegisterGateways()
    {
        $this->paymentGatewayRegister->registerGateway(Stripe::class);
        $this->paymentGatewayRegister->registerGateway(PayPal::class);

        $this->assertCount(2, $this->paymentGatewayRegister->getPaymentGateways());
    }

    /**
     * @unreleased
     * @throws Exception
     * @throws OverflowException
     */
    public function testRegisterAlreadyRegisteredPaymentGateway()
    {
        $this->paymentGatewayRegister->registerGateway(Stripe::class);
        $this->paymentGatewayRegister->registerGateway(Stripe::class);
        $this->expectException(OverflowException::class);
    }
}

class AuthorizeNet {
}

class Square extends PaymentGateway {
	/**
	 * @return string
	 */
	public static function id() {
        return 'square';
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
        return 'Square Payment Method';
    }

    public function getPaymentMethodLabel()
    {
        // TODO: Implement getPaymentMethodLabel() method.
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

class Stripe extends PaymentGateway
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

class Paypal extends PaymentGateway
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
