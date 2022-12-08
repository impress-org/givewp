<?php
namespace Give\Tests\Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Form;
use Give\Framework\FieldsAPI\Section;
use Give\Framework\FieldsAPI\Text;
use PHPUnit\Framework\TestCase;

final class RemoveNodeTest extends TestCase
{

    public function testRemoveNode()
    {
        $form = (new Form('form'))
            ->append(
                Section::make('form-section')
                    ->append(
                        Text::make('firstTextField'),
                        Text::make('secondTextField')
                    )
            );

        $form->remove('secondTextField');

        $this->assertEquals(1, $form->getNodeByName('form-section')->count());
    }
}
