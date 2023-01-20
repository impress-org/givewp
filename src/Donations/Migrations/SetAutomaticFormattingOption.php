<?php

declare(strict_types=1);

namespace Give\Donations\Migrations;

use Give\Framework\Migrations\Contracts\Migration;
use NumberFormatter;

class SetAutomaticFormattingOption extends Migration
{
    public static function id(): string
    {
        return 'set_automatic_formatting_option';
    }

    public static function timestamp(): int
    {
        return strtotime('2022-01-20');
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $hasIntlExtension = class_exists(NumberFormatter::class);

        give_update_option('auto_format_currency', $hasIntlExtension ? 'on' : false);
    }
}
