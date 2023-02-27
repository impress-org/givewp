<?php

namespace Give\Tests;

use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\TestTraits\AssertIsType;
use Give_Cache_Setting;
use WP_UnitTestCase;

/**
 * Give Unit Test Case
 *
 * Provides Give-specific setup/tear down/assert methods
 * and helper functions.
 *
 * @since 2.22.1 migrated Give_Unit_Test_Case to Give\Tests namespace
 * @since 1.0
 */
class TestCase extends WP_UnitTestCase
{
    use AssertIsType;

    /**
     * Cache Give setting
     * Note: we will use this variable to reset setting after each test to prevent test failure
     * which happen due to change in setting during test.
     *
     * @since 2.4.0
     * @var array
     */
    private static $saved_settings;

    /**
     * @inheritDoc
     *
     * @since 2.22.1 add setUpTraits
     * @since 1.0
     */
    public function setUp()
    {
        // Ensure server variable is set for WP email functions.
        if (!isset($_SERVER['SERVER_NAME'])) {
            $_SERVER['SERVER_NAME'] = 'localhost';
        }

        self::$saved_settings = Give_Cache_Setting::get_settings();

        parent::setUp();

        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['SERVER_NAME'] = '';
        $PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

        if (!defined('GIVE_USE_PHP_SESSIONS')) {
            define('GIVE_USE_PHP_SESSIONS', false);
        }

        $this->registerTestGateway();

        $this->setUpTraits();
    }

    /**
     *
     * @since 2.22.1 add setUpTraits
     * @since 1.0
     * @inheritDoc
     */
    public function tearDown()
    {
        // Reset Give setting to prevent failing test which happen we update setting in test function.
        update_option('give_settings', self::$saved_settings);

        parent::tearDown();

        $this->tearDownTraits();
    }

    /**
     * Strip newlines and tabs when using expectedOutputString() as otherwise.
     * the most template-related tests will fail due to indentation/alignment in.
     * the template not matching the sample strings set in the tests.
     *
     * @since 1.0
     */
    public function filter_output($output)
    {
        $output = preg_replace('/[\n]+/S', '', $output);
        $output = preg_replace('/[\t]+/S', '', $output);

        return $output;
    }

    /**
     * Asserts thing is not WP_Error.
     *
     * @since 1.0
     *
     * @param mixed $actual
     * @param string $message
     */
    public function assertNotWPError($actual, $message = '')
    {
        $this->assertNotInstanceOf('WP_Error', $actual, $message);
    }

    /**
     * Asserts thing is WP_Error.
     *
     * @param mixed $actual
     * @param string $message
     */
    public function assertIsWPError($actual, $message = '')
    {
        $this->assertInstanceOf('WP_Error', $actual, $message);
    }

    /**
     * Backport assertNotFalse to PHPUnit 3.6.12 which only runs in PHP 5.2.
     *
     * @since  1.0
     *
     * @param        $condition
     * @param string $message
     *
     * @return void
     */
    public static function assertNotFalse($condition, $message = '')
    {
        if (version_compare(phpversion(), '5.3', '<')) {
            self::assertThat($condition, self::logicalNot(self::isFalse()), $message);
        } else {
            parent::assertNotFalse($condition, $message);
        }
    }

    /**
     * Asserts that two Money objects are equal.
     *
     * @since 2.20.0
     *
     * @param Money $expected
     * @param Money $actual
     *
     * @return void
     */
    public static function assertMoneyEquals(Money $expected, Money $actual)
    {
        self::assertTrue(
            $expected->equals($actual),
            "Failed asserting money is equal. Expected: {$expected->getAmount()} {$expected->getCurrency()->getCode()}, Actual: {$actual->getAmount()} {$actual->getCurrency()->getCode()}"
        );
    }

    /**
     * A helper for creating a Mock (AKA stub or test double) with best practices. A callable may be provided which
     * applies further setup for the Mock Builder. If the mock is returned in the callable, it will be returned,
     * otherwise the mock will be generated.
     *
     * @see https://phpunit.de/manual/5.5/en/test-doubles.html
     *
     * @since 2.25.0 add suppression for phpunit internal deprecated notice
     * @since 2.11.0
     *
     * @param  string  $abstract  The class to create a mock for
     * @param  null|callable  $builderCallable  A callable for applying additional changes to the builder
     *
     * @return object
     */
    public function createMock($abstract, $builderCallable = null)
    {
        static::setSuppressedErrorHandler();

        $mockBuilder = $this->getMockBuilder($abstract)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes();

        if ($builderCallable !== null) {
            $mock = $builderCallable($mockBuilder);

            if (is_object($mock)) {
                return $mock;
            }
        }

        try {
            return $mockBuilder->getMock();
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Set error handler to suppress `ReflectionType::__toString()` deprecation warning
     *
     * @since 2.25.0
     *
     * @return void
     */
    public static function setSuppressedErrorHandler()
    {
        $previousHandler = set_error_handler(
            static function ($code, $description, $file = null, $line = null, $context = null) use (&$previousHandler) {
                if (($code & E_DEPRECATED)) {
                    return true;
                }

                return $previousHandler($code, $description, $file, $line, $context);
            }
        );
    }

    /**
     * A helper for creating a mock and binding it to the service container. This is especially useful for working with
     * Dependency Injection and other moments where the class being mocked is retrieved in some way from the Service
     * Container.
     *
     * @since 2.11.0
     *
     * @param  string  $abstract
     * @param  null|callable  $builderCallable
     *
     * @return object
     */
    public function mock($abstract, $builderCallable = null)
    {
        $mock = $this->createMock($abstract, $builderCallable);

        give()->singleton($abstract, function () use ($mock) {
            return $mock;
        });

        return $mock;
    }

    /**
     * * Interact with specific traits during setUp
     *
     * @since 2.22.1
     *
     * @return void
     */
    protected function setUpTraits()
    {
        //
    }

    /**
     * Interact with specific traits during tearDown
     *
     * @since 2.22.1
     *
     * @return void
     */
    protected function tearDownTraits()
    {
        $uses = array_flip(class_uses(static::class));

        if (isset($uses[RefreshDatabase::class])) {
            /** @var $this RefreshDatabase */
            $this->refreshDatabase();
        }
    }

    /**
     * Registers Test Gateway to be used in tests to avoid any side effects caused by gateway not being registered.
     *
     * @since 2.22.1
     *
     * @return void
     */
    private function registerTestGateway()
    {
        /** @var PaymentGatewayRegister $registrar */
        $registrar = give(PaymentGatewayRegister::class);

        if (!$registrar->hasPaymentGateway(TestGateway::id())) {
            $registrar->registerGateway(TestGateway::class);
        }
    }
}
