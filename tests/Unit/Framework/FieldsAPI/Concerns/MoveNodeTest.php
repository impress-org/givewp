<?php
namespace Give\Tests\Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Form;
use Give\Framework\FieldsAPI\Section;
use Give\Framework\FieldsAPI\Text;
use PHPUnit\Framework\TestCase;

final class MoveNodeTest extends TestCase
{

    public function testMoveAfter()
    {
        $form = (new Form('form'))
            ->append(
                Section::make('form-section')
                    ->append(
                        Text::make('firstTextField'),
                        Text::make('secondTextField')
                    )
            );

        $section = $form->getNodeByName('form-section');
        $section->move('firstTextField')->after('secondTextField');

        $this->assertEquals(1, $section->getNodeIndexByName('firstTextField'));
        $this->assertEquals(0, $section->getNodeIndexByName('secondTextField'));
    }
}
