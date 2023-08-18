<?php

namespace Give\Framework\FieldsAPI\Concerns;

use Closure;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\FieldsAPI\ValueObjects\PersistenceScope;

/**
 * This provides the ability to set a scope and meta key for a field. The scope is used to determine if and where the
 * field should be stored. The meta key is used to store the field in the database.
 *
 * @since 2.32.0
 */
trait HasPersistence
{
    /**
     * @var Closure
     */
    private $scopeCallback;

    /**
     * @since 2.32.0
     *
     * @var PersistenceScope|null
     */
    protected $scope;

    /**
     * @since 2.32.0
     *
     * @var string|null
     */
    protected $metaKey = null;

    /**
     * @since 2.32.0
     *
     * @param string|PersistenceScope|Closure $scope
     */
    public function scope($scope): self
    {
        if ($scope instanceof Closure) {
            $this->scopeCallback = $scope;
            $scope = PersistenceScope::callback();
        } elseif (is_string($scope)) {
            $scope = new PersistenceScope($scope);
        } elseif (!$scope instanceof PersistenceScope) {
            throw new InvalidArgumentException('Scope must be a string, Closure, or PersistenceScope');
        }

        $this->scope = $scope;

        return $this;
    }

    /**
     * @since 2.32.0
     */
    public function getScope(): PersistenceScope
    {
        return $this->scope;
    }

    /**
     * @since 2.32.0
     */
    public function getScopeValue(): string
    {
        return (string)$this->scope;
    }

    /**
     * @return Closure|null
     */
    public function getScopeCallback()
    {
        return $this->scopeCallback;
    }

    /**
     * @since 2.32.0
     */
    public function metaKey(string $metaKey): self
    {
        $this->metaKey = $metaKey;

        return $this;
    }

    /**
     * @since 2.32.0
     *
     * @return  string|null
     */
    public function getMetaKey()
    {
        return $this->metaKey;
    }

    /**
     * @since 2.32.0 updated to use scoping under the hood, no signature change
     * @since 2.28.0 added types
     * @since 2.10.2
     */
    public function storeAsDonorMeta(bool $storeAsDonorMeta = true)
    {
        $this->scope = $storeAsDonorMeta
            ? PersistenceScope::donor()
            : PersistenceScope::donation();

        return $this;
    }

    /**
     * @since 2.32.0 updated to use scoping under the hood, no signature change
     * @since 2.28.0 added types
     * @since 2.10.2
     */
    public function shouldStoreAsDonorMeta(): bool
    {
        return $this->scope->isDonor();
    }
}
