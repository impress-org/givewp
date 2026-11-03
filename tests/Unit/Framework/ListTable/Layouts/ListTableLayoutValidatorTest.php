<?php

namespace Give\Tests\Unit\Framework\ListTable\Layouts;

use Give\Framework\ListTable\Layouts\ListTableLayoutValidator;
use Give\Tests\TestCase;
use WP_Error;

/**
 * @since TBD
 */
class ListTableLayoutValidatorTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testReturnsTheNormalizedLayoutInAFixedKeyOrder(): void
    {
        $layout = (new ListTableLayoutValidator())->validate([
            'perPage' => 50,
            'density' => 'compact',
            'order' => ['donor', 'amount'],
            'visibility' => ['form' => false, 'amount' => true],
            'version' => 1,
        ]);

        $this->assertSame(
            [
                'version' => 1,
                'visibility' => ['form' => false, 'amount' => true],
                'order' => ['donor', 'amount'],
                'density' => 'compact',
                'perPage' => 50,
            ],
            $layout
        );
    }

    /**
     * @since TBD
     */
    public function testAcceptsALayoutWithOnlyTheVersion(): void
    {
        $this->assertSame(['version' => 1], (new ListTableLayoutValidator())->validate(['version' => 1]));
    }

    /**
     * @since TBD
     */
    public function testDropsEmptyVisibilityAndOrderBecauseTheyMeanNoStoredChoice(): void
    {
        $this->assertSame(
            ['version' => 1],
            (new ListTableLayoutValidator())->validate(['version' => 1, 'visibility' => [], 'order' => []])
        );
    }

    /**
     * @since TBD
     */
    public function testAcceptsEveryDensity(): void
    {
        $validator = new ListTableLayoutValidator();

        foreach (['compact', 'balanced', 'comfortable'] as $density) {
            $this->assertSame(
                ['version' => 1, 'density' => $density],
                $validator->validate(['version' => 1, 'density' => $density])
            );
        }
    }

    /**
     * @since TBD
     */
    public function testAcceptsPageSizesAtTheBounds(): void
    {
        $validator = new ListTableLayoutValidator();

        $this->assertSame(['version' => 1, 'perPage' => 1], $validator->validate(['version' => 1, 'perPage' => 1]));
        $this->assertSame(
            ['version' => 1, 'perPage' => 100],
            $validator->validate(['version' => 1, 'perPage' => 100])
        );
    }

    /**
     * @since TBD
     */
    public function testAcceptsFiftyEntries(): void
    {
        $ids = $this->ids(50);

        $layout = (new ListTableLayoutValidator())->validate([
            'version' => 1,
            'visibility' => array_fill_keys($ids, true),
            'order' => $ids,
        ]);

        $this->assertIsArray($layout);
        $this->assertCount(50, $layout['visibility']);
        $this->assertCount(50, $layout['order']);
    }

    /**
     * @since TBD
     *
     * @dataProvider invalidLayouts
     *
     * @param mixed $layout
     */
    public function testRejectsAnInvalidLayout($layout): void
    {
        $result = (new ListTableLayoutValidator())->validate($layout);

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertSame('give_list_table_invalid_layout', $result->get_error_code());
        $this->assertSame(['status' => 400], $result->get_error_data());
    }

    /**
     * @since TBD
     */
    public function invalidLayouts(): array
    {
        $ids = $this->ids(51);

        return [
            'not an array' => ['compact'],
            'null' => [null],
            'missing version' => [['density' => 'compact']],
            'version 2' => [['version' => 2]],
            'version as a string' => [['version' => '1']],
            'unknown key' => [['version' => 1, 'search' => 'jane']],
            'order id with a trailing newline' => [['version' => 1, 'order' => ["donor\n"]]],
            'visibility id with a trailing newline' => [['version' => 1, 'visibility' => ["form\n" => false]]],
            'longest id plus a trailing newline' => [['version' => 1, 'order' => [str_repeat('a', 64) . "\n"]]],
            'visibility not an array' => [['version' => 1, 'visibility' => 'form']],
            'visibility as a list' => [['version' => 1, 'visibility' => [true, false]]],
            'visibility value not boolean' => [['version' => 1, 'visibility' => ['form' => 0]]],
            'visibility id with markup' => [['version' => 1, 'visibility' => ['<script>' => false]]],
            'visibility id too long' => [['version' => 1, 'visibility' => [str_repeat('a', 65) => false]]],
            'visibility with 51 keys' => [['version' => 1, 'visibility' => array_fill_keys($ids, true)]],
            'order not an array' => [['version' => 1, 'order' => 'donor']],
            'order as a map' => [['version' => 1, 'order' => ['first' => 'donor']]],
            'order id not a string' => [['version' => 1, 'order' => [12]]],
            'order id with markup' => [['version' => 1, 'order' => ['<img src=x onerror=alert(1)>']]],
            'order id with a space' => [['version' => 1, 'order' => ['donor name']]],
            'order empty id' => [['version' => 1, 'order' => ['']]],
            'order duplicate' => [['version' => 1, 'order' => ['donor', 'donor']]],
            'order with 51 ids' => [['version' => 1, 'order' => $ids]],
            'density unknown' => [['version' => 1, 'density' => 'cozy']],
            'density not a string' => [['version' => 1, 'density' => 1]],
            'perPage zero' => [['version' => 1, 'perPage' => 0]],
            'perPage 101' => [['version' => 1, 'perPage' => 101]],
            'perPage as a string' => [['version' => 1, 'perPage' => '50']],
            'perPage as a float' => [['version' => 1, 'perPage' => 50.5]],
            'perPage as a boolean' => [['version' => 1, 'perPage' => true]],
            'density null' => [['version' => 1, 'density' => null]],
            'visibility null' => [['version' => 1, 'visibility' => null]],
        ];
    }

    /**
     * @since TBD
     *
     * @return string[]
     */
    private function ids(int $count): array
    {
        return array_map(static function (int $index): string {
            return "column-{$index}";
        }, range(1, $count));
    }
}
