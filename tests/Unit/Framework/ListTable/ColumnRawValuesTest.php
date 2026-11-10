<?php

namespace Give\Tests\Unit\Framework\ListTable;

use Exception;
use Give\Framework\ListTable\ColumnKind;
use Give\Framework\ListTable\ListTable;
use Give\Framework\ListTable\ModelColumn;
use Give\Framework\Models\Model;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class ColumnRawValuesTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testRowCarriesRawValuesNextToTheCells(): void
    {
        $table = $this->tableWith($this->moneyColumn());
        $table->items([$this->model()]);

        $row = $table->getItems()[0];

        $this->assertSame('$10.00', $row['money']);
        $this->assertSame(['amount' => '10.00', 'currency' => 'USD'], $row['rawValues']['money']);
    }

    /**
     * @since TBD
     */
    public function testColumnThatThrowsHasNoRawValue(): void
    {
        $table = $this->tableWith($this->moneyColumn(), $this->throwingColumn());
        $table->items([$this->model()]);

        $row = $table->getItems()[0];

        $this->assertArrayHasKey('throwing', $row);
        $this->assertArrayNotHasKey('throwing', $row['rawValues']);
        $this->assertArrayHasKey('money', $row['rawValues']);
    }

    /**
     * @since TBD
     */
    public function testRowWithoutRawValuesHasNoKey(): void
    {
        $table = $this->tableWith($this->textColumn());
        $table->items([$this->model()]);

        $this->assertArrayNotHasKey('rawValues', $table->getItems()[0]);
    }

    /**
     * @since TBD
     */
    public function testSchemaDeclaresTheKind(): void
    {
        $money = $this->moneyColumn();
        $text = $this->textColumn();
        $money->visible(true);
        $text->visible(true);

        $this->assertSame('money', $money->toArray()['kind']);
        $this->assertSame(ColumnKind::TEXT, $text->toArray()['kind']);
    }

    /**
     * @since TBD
     */
    private function model(): Model
    {
        return new class extends Model {
        };
    }

    /**
     * @since TBD
     */
    private function tableWith(ModelColumn ...$columns): ListTable
    {
        return new class($columns) extends ListTable {
            /** @var ModelColumn[] */
            private $testColumns;

            public function __construct(array $columns)
            {
                $this->testColumns = $columns;
                parent::__construct();
            }

            public function id(): string
            {
                return 'test';
            }

            protected function getDefaultColumns(): array
            {
                return $this->testColumns;
            }

            protected function getDefaultVisibleColumns(): array
            {
                return [];
            }
        };
    }

    /**
     * @since TBD
     */
    private function moneyColumn(): ModelColumn
    {
        return new class extends ModelColumn {
            public static function getId(): string
            {
                return 'money';
            }

            public function getLabel(): string
            {
                return 'Money';
            }

            public function getCellValue($model)
            {
                return '$10.00';
            }

            public function getKind(): string
            {
                return ColumnKind::MONEY;
            }

            public function getRawValue($model)
            {
                return ['amount' => '10.00', 'currency' => 'USD'];
            }
        };
    }

    /**
     * @since TBD
     */
    private function textColumn(): ModelColumn
    {
        return new class extends ModelColumn {
            public static function getId(): string
            {
                return 'text';
            }

            public function getLabel(): string
            {
                return 'Text';
            }

            public function getCellValue($model)
            {
                return 'Hello';
            }
        };
    }

    /**
     * @since TBD
     */
    private function throwingColumn(): ModelColumn
    {
        return new class extends ModelColumn {
            public static function getId(): string
            {
                return 'throwing';
            }

            public function getLabel(): string
            {
                return 'Throwing';
            }

            public function getCellValue($model)
            {
                throw new Exception('boom');
            }

            public function getRawValue($model)
            {
                return 'never reached';
            }
        };
    }
}
