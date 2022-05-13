<?php

namespace unit\tests\Framework\ListTable;

use Give\Donations\DonationsListTable;
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

        $this->assertEquals('custom', $customColumn->getName());
        $this->assertEquals('custom column text', $customColumn->getText());
    }

    /**
     * @return void
     * @throws ColumnIdCollisionException
     */
    public function testAddingExistingColumnShouldThrowException()
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
    public function testAddingColumnBeforeNoneExistingColumnShouldThrowException()
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
    public function testAddingColumnAfterNoneExistingColumnShouldThrowException()
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

        $listTable->removeColumn('id');

        $this->assertEquals(null, $listTable->getColumnByName('id'));
    }
}
