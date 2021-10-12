<?php

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\PaymentGateways\Contracts\PaymentGateway;
use Give\Framework\PaymentGateways\Exceptions\OverflowException;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\PaymentGateways\PaymentGatewayTypes\OffSitePaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayTypes\OnSitePaymentGateway;
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
		$this->assertTrue( $this->paymentGatewayRegister instanceof \Traversable );
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
		give( $this->paymentGatewayRegister->getPaymentGateway( Square::id() ) )->getOptions();
	}

	/**
	 * @unreleased
	 */
	public function testRegisterGateways() {
		$this->paymentGatewayRegister->registerGateway( Stripe::class );
		$this->paymentGatewayRegister->registerGateway( PayPal::class );

		$this->assertTrue( 2 === count( $this->paymentGatewayRegister->getPaymentGateways() ) );
	}

	/**
	 * @unreleased
	 * @throws Exception
	 * @throws OverflowException
	 */
	public function testRegisterAlreadyRegisteredPaymentGateway() {
		$this->expectException( OverflowException::class );

		$this->paymentGatewayRegister->registerGateway( Stripe::class );
		$this->paymentGatewayRegister->registerGateway( Stripe::class );
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
	public function getName() {
		return 'Stripe Payment Method';
	}

	/**
	 * @inheritDoc
	 */
	public function getPaymentMethodLabel() {
		return 'Credit Card';
	}

	/**
	 * @inheritDoc
	 */
	public function getOptions() {
		return [];
	}
}

class Square extends PaymentGateway {
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
	public function getName() {
		return 'Stripe Payment Method';
	}

	/**
	 * @inheritDoc
	 */
	public function getPaymentMethodLabel() {
		return 'Credit Card';
	}
}

class Stripe extends PaymentGateway implements OnSitePaymentGateway {
	/**
	 * @return string
	 */
	public static function id() {
		return 'stripe';
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
	public function getName() {
		return 'Stripe Payment Method';
	}

	/**
	 * @inheritDoc
	 */
	public function getPaymentMethodLabel() {
		return 'Credit Card';
	}

	/**
	 * @inheritDoc
	 */
	public function getOptions() {
		return [];
	}
}

class Paypal extends PaymentGateway implements OffSitePaymentGateway {
	/**
	 * @return string
	 */
	public static function id() {
		return 'paypal';
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
	public function getName() {
		return 'Stripe Payment Method';
	}

	/**
	 * @inheritDoc
	 */
	public function getPaymentMethodLabel() {
		return 'Credit Card';
	}

	/**
	 * @inheritDoc
	 */
	public function getOptions() {
		return [];
	}
}
