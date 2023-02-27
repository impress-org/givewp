<?php

declare(strict_types=1);

namespace Give\Tests\Unit\Framework\FieldsAPI\Actions;

use Give\Framework\FieldsAPI\Actions\CreateValidatorFromForm;
use Give\Framework\FieldsAPI\Section;
use Give\Tests\TestCase;
use Give\Framework\FieldsAPI\Form;

/**
 * @covers CreateValidatorFromForm
 */
class CreateValidatorFromFormTest extends TestCase
{
    /**
     * @since 2.25.0
     */
    public function testShouldCreateValidatorFromForm()
    {
        // create a basic Form
        $form = new Form('Test Form');
        $section = new Section('Test Section');

        $section->append(
            give_field('text', 'testField')
                ->label('Test Field')
                ->required()
        );

        $form->append($section);

        // create a validator from the form
        $validator = (new CreateValidatorFromForm())($form, [
            'testField' => 'testValue',
        ]);

        self::assertTrue($validator->passes());
    }
}
