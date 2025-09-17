<?php

namespace Give\Tests\PHPStan;

use Give\PHPStan\Query\Rules\RequireLimitBeforeFetchingRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

final class RequireLimitBeforeFetchingRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        // Set to true to also enforce limit(1) for get()
        return new RequireLimitBeforeFetchingRule(false);
    }

    public function testFlagsGetAllWithoutLimit(): void
    {
        $this->analyse([
            __DIR__ . '/fixtures/NoLimitGetAll.php',
        ], [
            [
                'QueryBuilder::getAll() called without limit()/paginate() in the chain. Add ->limit($n)/->paginate(...), or ->limit(0) if intentionally unbounded.',
                11,
            ],
        ]);
    }

    public function testPassesWithLimit(): void
    {
        $this->analyse([
            __DIR__ . '/fixtures/WithLimitGetAll.php',
        ], []);
    }

    public function testPassesWithLimitZero(): void
    {
        $this->analyse([
            __DIR__ . '/fixtures/WithUnlimitedGetAll.php',
        ], []);
    }
}


