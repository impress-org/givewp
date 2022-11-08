<?php

namespace unit\tests\Framework\ListTable;

use Give\Donations\ListTable\DonationsListTable;
use Give\Framework\ListTable\Column;
use Give\Framework\ListTable\Exceptions\ColumnIdCollisionException;
use Give\Framework\ListTable\Exceptions\ReferenceColumnNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @unreleased
 *
 * @covers Column
 */
final class DonationsListTableColumnsTest extends TestCase
{
    /**
     * @return void
     * @throws ColumnIdCollisionException
     */
    public function testItShouldAddCustomColumnToDonationsListTableColumns()
    {
        $listTable = give(DonationsListTable::class);

        $this->assertEquals(null, $listTable->getColumnByName('custom'));

        $listTable->addColumn(
            Column::name('custom')
                ->text('custom column text')
        );

        $customColumn = $listTable->getColumnByName('custom');

        $this->assertInstanceOf(Column::class, $customColumn);
    }

    /**
     * @return void
     * @throws ColumnIdCollisionException
     */
    public function testAddingColumnWithExistingNameShouldThrowException()
    {
        $this->expectException(ColumnIdCollisionException::class);

        $listTable = give(DonationsListTable::class);

        $listTable->addColumn(
            Column::name('id')
        );
    }

    /**
     * @return void
     * @throws ReferenceColumnNotFoundException
     */
    public function testItShouldAddColumnBeforeSpecificColumn()
    {
        $listTable = give(DonationsListTable::class);

        $listTable->addColumnBefore(
            'formTitle',
            Column::name('custom')
        );

        $formTitleColumnIndex = $listTable->getColumnIndexByName('formTitle');
        $customColumnIndex = $listTable->getColumnIndexByName('custom');

        $expectedIndex = $formTitleColumnIndex - 1;

        $this->assertEquals($expectedIndex, $customColumnIndex);
    }

    /**
     * @return void
     * @throws ReferenceColumnNotFoundException
     */
    public function testItShouldAddColumnAfterSpecificColumn()
    {
        $listTable = give(DonationsListTable::class);

        $listTable->addColumnAfter(
            'formTitle',
            Column::name('custom2')
        );

        $formTitleColumnIndex = $listTable->getColumnIndexByName('formTitle');
        $customColumnIndex = $listTable->getColumnIndexByName('custom2');

        $expectedIndex = $formTitleColumnIndex + 1;

        $this->assertEquals($expectedIndex, $customColumnIndex);
    }

    /**
     * @return void
     * @throws ReferenceColumnNotFoundException
     */
    public function testAddingColumnBeforeNonExistentColumnShouldThrowException()
    {
        $this->expectException(ReferenceColumnNotFoundException::class);

        $listTable = give(DonationsListTable::class);

        $listTable->addColumnBefore(
            'nonExistingColumn',
            Column::name('customColumn')
        );
    }

    /**
     * @return void
     * @throws ReferenceColumnNotFoundException
     */
    public function testAddingColumnAfterNonExistentColumnShouldThrowException()
    {
        $this->expectException(ReferenceColumnNotFoundException::class);

        $listTable = give(DonationsListTable::class);

        $listTable->addColumnAfter(
            'nonExistingColumn',
            Column::name('customColumn')
        );
    }

    /**
     * @return void
     * @throws ReferenceColumnNotFoundException
     */
    public function testItShouldRemoveExistingColumn()
    {
        $listTable = give(DonationsListTable::class);

        $this->assertInstanceOf(Column::class, $listTable->getColumnByName('id'));

        $listTable->removeColumn('id');

        $this->assertEquals(null, $listTable->getColumnByName('id'));
    }

    /**
     * @return void
     * @throws ColumnIdCollisionException
     */
    public function testCustomColumnDefinedProperties()
    {
        $listTable = give(DonationsListTable::class);

        $listTable->addColumn(
            Column::name('test')
                ->text('Test Column')
                ->visible(true)
                ->sortable(true)
                ->filterable(true)
                ->defaultValue('test value')
        );

        $column = $listTable->getColumnByName('test');

        $this->assertInstanceOf(Column::class, $column);

        $this->assertEquals('test', $column->getName());
        $this->assertEquals('Test Column', $column->getText());
        $this->assertEquals('test value', $column->getDefaultValue());
        $this->assertEquals(true, $column->isVisible());
        $this->assertEquals(true, $column->isSortable());
        $this->assertEquals(true, $column->isFilterable());
    }

    /**
     * @return void
     */
    public function testItShouldFilterColumnRowValue()
    {
        $items = [
            [
                'id' => 1,
                'formTitle' => 'Donation Form 1',
            ],
            [
                'id' => 2,
                'formTitle' => 'Donation Form 2',
            ]
        ];

        $listTable = give(DonationsListTable::class);

        $formTitleColumn = $listTable->getColumnByName('formTitle');

        $formTitleColumn->filter(static function ($value, $row) {
            return $value . ' updated';
        });

        $listTable->items($items);

        $listTableItems = $listTable->getItems();

        $this->assertEquals('Donation Form 1 updated', $listTableItems[0]['formTitle']);
        $this->assertEquals('Donation Form 2 updated', $listTableItems[1]['formTitle']);
    }
}
