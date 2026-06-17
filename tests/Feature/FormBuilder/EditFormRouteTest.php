<?php

namespace Give\Tests\Feature\FormBuilder;

use Give\FormBuilder\Routes\EditFormRoute;
use Give\Tests\TestCase;

/**
 * Tests for EditFormRoute to verify defensive input validation on $_GET['post'].
 *
 * @see https://github.com/impress-org/givewp/issues/8229
 *
 * @since 4.15.0
 */
class EditFormRouteTest extends TestCase
{
    /**
     * Confirm that a non-numeric post ID (e.g. a hexadecimal value used by Contact
     * Form 7) does not cause a PHP fatal TypeError when abs() is called.
     *
     * Prior to the fix, abs('5c80d03') threw: "abs(): Argument #1 ($num) must be
     * of type int|float, string given" on PHP 8.x, crashing every admin page that
     * CF7 or similar plugins opened with a hex post ID in the query string.
     *
     * @since 4.15.0
     *
     * @return void
     */
    public function testInvokeWithNonNumericPostIdDoesNotThrowTypeError(): void
    {
        $_GET['post']   = '5c80d03'; // Hexadecimal ID as used by Contact Form 7.
        $_GET['action'] = 'edit';

        try {
            ( new EditFormRoute() )();
        } catch ( \TypeError $e ) {
            $this->fail(
                'EditFormRoute threw a TypeError for non-numeric post ID: ' . $e->getMessage()
            );
        } finally {
            unset( $_GET['post'], $_GET['action'] );
        }

        $this->assertTrue( true, 'No TypeError was thrown for non-numeric post ID.' );
    }

    /**
     * Confirm that the route also handles an array post ID gracefully (the bulk
     * action case that the original is_array() guard was written for).
     *
     * @since 4.15.0
     *
     * @return void
     */
    public function testInvokeWithArrayPostIdIsSkipped(): void
    {
        $_GET['post']   = [ '123', '456' ];
        $_GET['action'] = 'edit';

        try {
            ( new EditFormRoute() )();
        } catch ( \TypeError $e ) {
            $this->fail(
                'EditFormRoute threw a TypeError for array post ID: ' . $e->getMessage()
            );
        } finally {
            unset( $_GET['post'], $_GET['action'] );
        }

        $this->assertTrue( true, 'Array post ID was handled gracefully.' );
    }
}
