<?php
namespace Give\Tests\Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Form;
use Give\Framework\FieldsAPI\Section;
use Give\Framework\FieldsAPI\Text;
use PHPUnit\Framework\TestCase;

final class RemoveNodeTest extends TestCase
{
    /**
     * @since 2.22.0
     */
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

    /**
     * @since 3.0.0
     */
    public function testRemoveNodeKeepsArrayNumeric()
    {
        $form = (new Form('form'))
            ->append(
                Section::make('form-section')
                    ->append(
                        Text::make('firstTextField'),
                        Text::make('secondTextField')
                    )
            );

        $form->remove('firstTextField');

        /** @var Section $section */
        $section = $form->getNodeByName('form-section');
        $nodes = $section->all();

        $this->assertCount(1, $nodes);
        $this->assertEquals(array_values($nodes), $nodes);
    }
}
