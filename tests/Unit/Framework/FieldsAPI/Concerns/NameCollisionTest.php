<?php
namespace Give\Tests\Unit\Framework\FieldsAPI\Concerns;

namespace unit\tests\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Exceptions\NameCollisionException;
use Give\Framework\FieldsAPI\Form;
use Give\Framework\FieldsAPI\Group;
use Give\Framework\FieldsAPI\Section;
use Give\Framework\FieldsAPI\Text;
use PHPUnit\Framework\TestCase;

final class NameCollisionTest extends TestCase
{

    public function testCheckNameCollision()
    {
        $this->expectException(NameCollisionException::class);

        (new Form('form'))
            ->append(
                Section::make('form-section')
                    ->append(
                        Text::make('textField'),
                        Text::make('textField')
                    )
            );
    }

    public function testCheckNameCollisionDeep()
    {
        $this->expectException(NameCollisionException::class);

        (new Form('form'))
            ->append(
                Section::make('form-section')
                    ->append(
                        Text::make('textField'),
                        Group::make('group')
                            ->append(
                                Text::make('textField')
                            )
                    )
            );
    }
}
