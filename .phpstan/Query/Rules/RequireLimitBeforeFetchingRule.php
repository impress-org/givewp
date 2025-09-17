<?php

namespace Give\PHPStan\Query\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\Type;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Flags QueryBuilder/ModelQueryBuilder fetch calls without an explicit limit or pagination.
 *
 * - getAll(): requires ->limit(...) or ->paginate(...)
 * - get(): optional enforcement (configurable)
 * - count(): ignored
 *
 * @implements Rule<MethodCall>
 */
final class RequireLimitBeforeFetchingRule implements Rule
{
    /**
     * @var bool
     */
    private $enforceLimitForGet;

    public function __construct(bool $enforceLimitForGet = false)
    {
        $this->enforceLimitForGet = $enforceLimitForGet;
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof MethodCall) {
            return [];
        }

        // Only consider known QueryBuilder-like receivers to avoid false positives
        $receiverType = $scope->getType($node->var);
        if (!$this->isQueryBuilderLike($receiverType)) {
            return [];
        }

        $methodName = $this->getMethodName($node->name);
        if ($methodName === null) {
            return [];
        }

        $isGetAll = $methodName === 'getAll';
        $isGet = $methodName === 'get';
        $isCount = $methodName === 'count';

        // Only care about fetch terminals
        if ($isCount) {
            return [];
        }
        if (!$isGetAll && !($this->enforceLimitForGet && $isGet)) {
            return [];
        }

        // Try to collect the fluent chain to detect prior limit()/paginate()
        $chain = $this->collectMethodChain($node);

        $hasLimit = in_array('limit', $chain, true);
        $hasPaginate = in_array('paginate', $chain, true);

        // Consider explicit unlimited reads acceptable via ->limit(0)
        if ($hasLimit || $hasPaginate) {
            return [];
        }

        // If we can't see the chain (e.g., variable usage), we still flag conservatively.
        if ($isGetAll) {
            return [
                RuleErrorBuilder::message('QueryBuilder::getAll() called without limit()/paginate() in the chain. Add ->limit($n)/->paginate(...), or ->limit(0) if intentionally unbounded.')
                    ->line($node->getLine())
                    ->build(),
            ];
        }

        if ($this->enforceLimitForGet && $isGet) {
            return [
                RuleErrorBuilder::message('QueryBuilder::get() called without limit(1)/paginate() in the chain. Add ->limit(1) for single-row fetch.')
                    ->line($node->getLine())
                    ->build(),
            ];
        }

        return [];
    }

    /**
     * @param Type $type
     */
    private function isQueryBuilderLike(Type $type): bool
    {
        $candidates = [
            'Give\\Framework\\QueryBuilder\\QueryBuilder',
            'Give\\Framework\\Models\\ModelQueryBuilder',
            'Give\\Donors\\Models\\DonorModelQueryBuilder',
            'Give\\Framework\\Database\\DB',
        ];

        foreach ($candidates as $class) {
            if ($type->isInstanceOf($class)->yes()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Walks backwards through a fluent method chain collecting method names.
     * This is a best-effort approach and will not resolve variable boundaries.
     *
     * @return string[]
     */
    private function collectMethodChain(MethodCall $node): array
    {
        $names = [];
        $current = $node;

        while ($current instanceof MethodCall) {
            $name = $this->getMethodName($current->name);
            if ($name !== null) {
                $names[] = $name;
            }

            $var = $current->var;
            if ($var instanceof MethodCall) {
                $current = $var;
                continue;
            }

            if ($var instanceof StaticCall) {
                $root = $this->getMethodName($var->name);
                if ($root !== null) {
                    $names[] = $root;
                }
            }

            break;
        }

        return array_reverse($names);
    }

    /**
     * @param Node\Identifier|Node\Name|Node\Expr $name
     */
    private function getMethodName($name): ?string
    {
        if ($name instanceof Node\Identifier) {
            return $name->name;
        }
        if (is_string($name)) {
            return $name;
        }
        return null;
    }
}


