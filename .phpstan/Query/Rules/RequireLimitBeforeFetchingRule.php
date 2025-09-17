<?php

namespace Give\PHPStan\Query\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\Type;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

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
    /**
     * @var int
     */
    private $lookBackStatements;
    /**
     * @var string[] directories whose files should be ignored by this rule
     */
    private $excludedDirectories;

    public function __construct(bool $enforceLimitForGet = false, int $lookBackStatements = 3, array $excludedDirectories = [])
    {
        $this->enforceLimitForGet = $enforceLimitForGet;
        $this->lookBackStatements = max(0, $lookBackStatements);
        $this->excludedDirectories = $excludedDirectories;
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

        // Respect excluded directories
        $analysedFile = $scope->getFile();
        if ($this->isInExcludedDirectory($analysedFile)) {
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

        // Look back at nearby statements for the same receiver with limit()/paginate()
        if ($this->hasLimitInLookBack($node)) {
            return [];
        }

        // If we can't see the chain (e.g., variable usage), we still flag conservatively.
        if ($isGetAll) {
            return [
                RuleErrorBuilder::message('QueryBuilder::getAll() called without limit()/paginate() in the chain. Add ->limit($n)/->paginate(...), or ->limit(0) if intentionally unbounded.')->identifier('give.requireLimitBeforeFetching')
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
            if ($type->isSuperTypeOf(new ObjectType($class))->yes()) {
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

    /**
     * Returns true if the analysed file resides under any excluded directory.
     */
    private function isInExcludedDirectory(string $filePath): bool
    {
        if (empty($this->excludedDirectories)) {
            return false;
        }

        $normalizedFile = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $filePath);

        foreach ($this->excludedDirectories as $dir) {
            if (!is_string($dir) || $dir === '') {
                continue;
            }
            $normalizedDir = rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $dir), DIRECTORY_SEPARATOR);
            if ($normalizedDir === '') {
                continue;
            }

            // Substring match is sufficient given project-root absolute paths in PHPStan
            if (strpos($normalizedFile, $normalizedDir) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Heuristic: if the limit()/paginate() is set in a separate prior statement
     * on the same variable (e.g., $query), accept it.
     */
    private function hasLimitInLookBack(MethodCall $fetchCall): bool
    {
        // Only support simple variable receivers for now (e.g., $query)
        $receiverVarName = $this->extractRootVariableName($fetchCall->var);
        if ($receiverVarName === null) {
            return false;
        }

        $stmt = $this->findEnclosingStatement($fetchCall);
        if ($stmt === null) {
            return false;
        }

        [$siblings, $index] = $this->findSiblingStatementsAndIndex($stmt);
        if ($siblings === null || $index === null) {
            return false;
        }

        // Scan up to N previous statements (configurable)
        $start = max(0, $index - $this->lookBackStatements);
        for ($i = $index - 1; $i >= $start; $i--) {
            $prevStmt = $siblings[$i];
            if (!$prevStmt instanceof Node\Stmt\Expression) {
                continue;
            }

            $expr = $prevStmt->expr;

            // Case 1: direct method call like $query->limit(...);
            if ($expr instanceof MethodCall) {
                $rootName = $this->extractRootVariableName($expr->var);
                if ($rootName !== $receiverVarName) {
                    continue;
                }
                $names = $this->collectMethodChain($expr);
                if (in_array('limit', $names, true) || in_array('paginate', $names, true)) {
                    return true;
                }
                continue;
            }

            // Case 2: assignment like $query = $query->limit(...);
            if ($expr instanceof Node\Expr\Assign) {
                $leftName = $this->extractRootVariableName($expr->var);
                if ($leftName !== $receiverVarName) {
                    continue;
                }

                if ($expr->expr instanceof MethodCall) {
                    $rightRoot = $this->extractRootVariableName($expr->expr->var);
                    if ($rightRoot !== $receiverVarName) {
                        continue;
                    }
                    $names = $this->collectMethodChain($expr->expr);
                    if (in_array('limit', $names, true) || in_array('paginate', $names, true)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Find the statement that directly contains the given node.
     */
    private function findEnclosingStatement(Node $node): ?Node\Stmt
    {
        $current = $node;
        // PHPStan provides parent attributes; walk up until we hit a Stmt
        for ($depth = 0; $depth < 10; $depth++) {
            $parent = $current->getAttribute('parent');
            if (!$parent instanceof Node) {
                return null;
            }
            if ($parent instanceof Node\Stmt) {
                return $parent;
            }
            $current = $parent;
        }
        return null;
    }

    /**
     * Return the sibling statements list and the index of the provided statement within it.
     * Searches up a few ancestor levels to find a container with a `stmts` array containing the statement.
     *
     * @return array{0: ?array, 1: ?int}
     */
    private function findSiblingStatementsAndIndex(Node\Stmt $stmt): array
    {
        $container = $stmt->getAttribute('parent');
        for ($depth = 0; $depth < 10 && $container instanceof Node; $depth++) {
            // Many containers (ClassMethod, Function_, If_, Else_, Case_, etc.) expose a public `stmts` array
            if (property_exists($container, 'stmts') && is_array($container->stmts)) {
                $stmts = $container->stmts;
                foreach ($stmts as $idx => $candidate) {
                    if ($candidate === $stmt) {
                        return [$stmts, $idx];
                    }
                }
            }
            $container = $container->getAttribute('parent');
        }

        return [null, null];
    }

    /**
     * Extract the base variable name from an expression chain (e.g., `$query`, `$this->query` -> `query`).
     * Only returns simple variable names; returns null for complex receivers.
     */
    private function extractRootVariableName($expr): ?string
    {
        $current = $expr;
        for ($i = 0; $i < 10 && $current instanceof Node\Expr; $i++) {
            if ($current instanceof Node\Expr\Variable) {
                return is_string($current->name) ? $current->name : null;
            }
            if ($current instanceof MethodCall || $current instanceof Node\Expr\PropertyFetch) {
                $current = $current->var;
                continue;
            }
            break;
        }
        return null;
    }
}


