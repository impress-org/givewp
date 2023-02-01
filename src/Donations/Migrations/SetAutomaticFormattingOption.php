<?php

declare(strict_types=1);

namespace Give\Donations\Migrations;

use Give\Framework\Migrations\Contracts\Migration;
use NumberFormatter;

/**
 * Sets the initial automatic formatting option based on whether the intl extension is installed.
 *
 * @since 2.24.2
 */
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
