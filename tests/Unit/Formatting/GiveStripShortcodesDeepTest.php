<?php

namespace Give\Tests\Unit\Formatting;

use Give\Tests\TestCase;

/**
 * @covers ::give_strip_shortcodes_deep
 *
 * @since TBD
 */
final class GiveStripShortcodesDeepTest extends TestCase
{
    /**
     * A registered shortcode tag used to build deterministic payloads.
     */
    const TAG = 'svultag';

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        add_shortcode(self::TAG, static function () {
            return 'RENDERED';
        });
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        remove_shortcode(self::TAG);

        parent::tearDown();
    }

    /**
     * A single strip_shortcodes() pass leaves a live tag behind; the deep strip must not.
     *
     * @since TBD
     */
    public function testRemovesSelfNestedShortcodeThatSurvivesASinglePass(): void
    {
        /*
         * After one pass the inner registered tag is removed and the remainder
         * recombines into a valid tag: [svul[svultag]tag] -> [svultag] -> ''.
         */
        $nested = '[svul[' . self::TAG . ']tag]';

        $this->assertNotSame('', strip_shortcodes($nested));
        $this->assertSame('', give_strip_shortcodes_deep($nested));
    }

    /**
     * @since TBD
     */
    public function testRemovesPlainRegisteredShortcode(): void
    {
        $this->assertSame('', give_strip_shortcodes_deep('[' . self::TAG . ']'));
    }

    /**
     * @since TBD
     */
    public function testLeavesUnregisteredBracketTextUnchanged(): void
    {
        $input = 'Ada [not_a_shortcode] Lovelace';

        $this->assertSame($input, give_strip_shortcodes_deep($input));
    }

    /**
     * @since TBD
     */
    public function testLeavesBenignTextUnchanged(): void
    {
        $input = 'John "JD" O\'Brien';

        $this->assertSame($input, give_strip_shortcodes_deep($input));
    }

    /**
     * Running the strip twice must equal running it once.
     *
     * @since TBD
     */
    public function testIsIdempotent(): void
    {
        $input = '[svul[' . self::TAG . ']tag]Acme';

        $once = give_strip_shortcodes_deep($input);
        $twice = give_strip_shortcodes_deep($once);

        $this->assertSame($once, $twice);
    }

    /**
     * @since TBD
     */
    public function testReturnsInputUnchangedWhenNoBracketPresent(): void
    {
        $this->assertSame('plain name', give_strip_shortcodes_deep('plain name'));
        $this->assertSame('', give_strip_shortcodes_deep(''));
    }

    /**
     * Legacy query rows can hand a null value; the helper must not fatal.
     *
     * @since TBD
     */
    public function testCastsNonStringInputToString(): void
    {
        $this->assertSame('', give_strip_shortcodes_deep(null));
    }
}
