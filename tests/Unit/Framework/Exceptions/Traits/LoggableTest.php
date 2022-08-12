<?php

namespace GiveTests\Unit\Framework\Exceptions\Traits;

use GiveTests\TestCase;

/**
 * @unreleased
 */
class LoggableTest extends TestCase
{
    /**
     * @unreleased
     */
    public function testExceptionHasContext()
    {
        $e = new MockLoggableException('This is a loggable exception.', 42);

        $logContext = $e->getLogContext();

        // @NOTE: Not asserting the logged exception "Line" because the value is coupled to the test code itself.
        $this->assertEquals('LoggableTest.php', $logContext['exception']['File']);
        $this->assertEquals('This is a loggable exception.', $logContext['exception']['Message']);
        $this->assertEquals(42, $logContext['exception']['Code']);
        $this->arrayHasKey('Line')->evaluate($logContext['exception']);
    }
}

/**
 * @unreleased
 */
class MockLoggableException extends \Give\Framework\Exceptions\Primitives\Exception
{
}
