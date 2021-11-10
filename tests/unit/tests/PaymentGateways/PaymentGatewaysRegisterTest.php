<?php

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\PaymentGateways\Contracts\PaymentGateway;
use Give\Framework\PaymentGateways\Exceptions\OverflowException;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use PHPUnit\Framework\TestCase;

/**
 * @unreleased
 */
class PaymentGatewaysRegisterTest extends TestCase {
	/**
	 * @var PaymentGatewayRegister
	 */
	private $paymentGatewayRegister;

	/**
	 * @unreleased
	 */
	public function __construct() {
		parent::__construct();
		$this->paymentGatewayRegister = give( PaymentGatewayRegister::class );
	}

	/**
	 * @unreleased
	 */
	public function testPaymentGatewayRegistererIsTraversable() {
		$this->assertInstanceOf( \Traversable::class, $this->paymentGatewayRegister );
	}

	/**
	 * @unreleased
	 * @throws Exception
	 * @throws OverflowException
	 */
	public function testNewPaymentGatewayClassMustExtendPaymentGateway() {
		$this->expectException( InvalidArgumentException::class );
		$this->paymentGatewayRegister->registerGateway( AuthorizeNet::class );
	}

	/**
	 * @unreleased
	 * @throws Exception
	 * @throws OverflowException
	 */
	public function testNewPaymentGatewayClassMustImplementPaymentGatewayTypeInterface() {
		$this->expectException( InvalidArgumentException::class );
		$this->paymentGatewayRegister->registerGateway( GoCardLess::class );
	}

	/**
	 * @unreleased
	 * @throws Exception
	 * @throws OverflowException
	 */
	public function testNewPaymentGatewayClassHasAllRequiredMembers() {
		$this->expectException( InvalidArgumentException::class );
		$this->paymentGatewayRegister->registerGateway( Square::class );
		give( $this->paymentGatewayRegister->getPaymentGateway( Square::id() ) )->getPaymentMethodLabel();
	}

	/**
	 * @unreleased
	 */
	public function testRegisterGateways() {
		$this->paymentGatewayRegister->registerGateway( Stripe::class );
		$this->paymentGatewayRegister->registerGateway( PayPal::class );

		$this->assertCount( 2, $this->paymentGatewayRegister->getPaymentGateways() );
	}

	/**
	 * @unreleased
	 * @throws Exception
	 * @throws OverflowException
	 */
	public function testRegisterAlreadyRegisteredPaymentGateway() {
		$this->expectException( OverflowException::class );

		$this->paymentGatewayRegister->registerGateway( Stripe::class );
	}

	/**
	 * @unreleased
	 */
	public function testAddNewPaymentGatewaysToList() {
		$gateways = give_get_payment_gateways();

		$this->assertArrayHasKey( Stripe::id(), $gateways );
	}
}

class AuthorizeNet {
}

class GoCardLess extends PaymentGateway {
	/**
	 * @return string
	 */
	public static function id() {
		return 'go-cardless';
	}

	/**
	 * @inheritDoc
	 */
	public function getId() {
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
