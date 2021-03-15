<?php

use PHPUnit\Framework\TestCase;
use Give\Onboarding\SettingsRepositoryFactory;

final class SettingsRepositoryFactoryTest extends TestCase {

	public function testCatchTypeErrorException() {

        $optionName = 'optionThatIsNotAnArrayAndDoesNotTriggerADefaultValue';
        update_option( $optionName, true );

        $factory = new SettingsRepositoryFactory;
        $factory->make( $optionName );

        // There is not a declarative method for testing the absence of an exception,
        // but we can imperically test that an exception is not thrown by asserting true.
        $this->assertTrue( true );
	}
}
