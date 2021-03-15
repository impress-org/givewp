<?php

use PHPUnit\Framework\TestCase;
use Give\Onboarding\SettingsRepositoryFactory;

final class SettingsRepositoryFactoryTest extends TestCase {

        public function testCatchTypeErrorException() {

                $this->expectException( PHPUnit_Framework_Error::class );

                $optionName = 'optionThatIsNotAnArrayAndDoesNotTriggerADefaultValue';
                update_option( $optionName, true );

                $factory = new SettingsRepositoryFactory;
                $factory->make( $optionName );
        }
}
