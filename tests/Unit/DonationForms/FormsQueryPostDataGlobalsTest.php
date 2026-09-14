<?php

namespace Give\Tests\Unit\DonationForms;

use Faker\Factory;
use Faker\Generator;
use Give\DonationForms\Models\DonationForm;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Forms_Query;

/**
 * @since TBD
 *
 * @covers Give_Forms_Query::get_forms
 */
class FormsQueryPostDataGlobalsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     *
     * @var Generator
     */
    private $faker;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        unset($GLOBALS['id']);

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testDoesNotLeaveTheFormIdInTheGlobalScope(): void
    {
        DonationForm::factory()->create();

        $unrelatedId = $this->faker->numberBetween(1, 1000);
        $GLOBALS['id'] = $unrelatedId;

        $loopIterations = 0;
        add_filter(
            'give_form',
            static function ($form) use (&$loopIterations) {
                $loopIterations++;

                return $form;
            }
        );

        $forms = (new Give_Forms_Query())->get_forms();

        /*
         * get_forms() returns a cached result without looping, in which case it cannot pollute
         * anything and the assertion below would hold for the wrong reason. Proving the loop ran is
         * what makes this a regression test.
         */
        $this->assertGreaterThan(0, $loopIterations, 'The results loop did not run, so nothing was exercised');
        $this->assertCount($loopIterations, $forms);
        $this->assertSame($unrelatedId, $GLOBALS['id']);
    }
}
