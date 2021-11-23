<?php

namespace Give\Framework\FieldsAPI;

use ReflectionClass;

/**
 * @since 2.12.0
 * @since 2.12.2 add Form, Group, and Html
 * @since 2.14.0 add Types::all static method
 */
class Types
{

    const CHECKBOX = Checkbox::TYPE;
    const DATE = Date::TYPE;
    const EMAIL = Email::TYPE;
    const FILE = File::TYPE;
    const FORM = Form::TYPE;
    const GROUP = Group::TYPE;
    const HIDDEN = Hidden::TYPE;
    const HTML = Html::TYPE;
    const PHONE = Phone::TYPE;
    const RADIO = Radio::TYPE;
    const SELECT = Select::TYPE;
    const TEXT = Text::TYPE;
    const TEXTAREA = Textarea::TYPE;
    const URL = Url::TYPE;

    /**
     * Get all the type strings in an array.
     *
     * @since 2.14.0
     *
     * @return string[]
     */
    public static function all()
    {
        $reflection = new ReflectionClass(static::class);

        return array_values($reflection->getConstants());
    }
}
