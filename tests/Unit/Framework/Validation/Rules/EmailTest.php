<?php

declare(strict_types=1);

namespace Give\Tests\Unit\Framework\Validation\Rules;

use Give\Framework\Validation\Rules\Email;
use Give\Tests\TestCase;

class EmailTest extends TestCase
{
    /**
     * @unreleased
     *
     * @dataProvider emailsProvider
     */
    public function testEmailRule($email, bool $shouldBeValid)
    {
        $rule = new Email();

        if ($shouldBeValid) {
            self::assertValidationRulePassed($rule, $email);
        } else {
            self::assertValidationRuleFailed($rule, $email);
        }
    }

    /**
     * @unreleased
     *
     * @return array<int, array<mixed, bool>>
     */
    public function emailsProvider(): array
    {
        return [
            // Valid emails
            ['jason.adams@givewp.com', true],
            ['bill123@example.com', true],

            // Invalid emails
            [true, false],
            [123, false],
            ['jason.adams', false],
            ['jason.adams@', false],
            ['jason.adams@givewp', false],
            ['jason.adams@givewp.', false],
        ];
    }
}
