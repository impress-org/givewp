<?php

namespace Give\Framework\FieldsAPI\Concerns;

use Closure;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\FieldsAPI\ValueObjects\PersistenceScope;

/**
 * This provides the ability to set a scope and meta key for a field. The scope is used to determine if and where the
 * field should be stored. The meta key is used to store the field in the database.
 *
 * @unreleased
 */
trait HasPersistence
{
    /**
     * @var Closure
     */
    private $scopeCallback;

    /**
     * @unreleased
     *
     * @var PersistenceScope|null
     */
    protected $scope;

    /**
     * @unreleased
     *
     * @var string|null
     */
    protected $metaKey = null;

    /**
     * @unreleased
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
     * @unreleased
     */
    public function getScope(): PersistenceScope
    {
        return $this->scope;
    }

    /**
     * @unreleased
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
     * @unreleased
     */
    public function metaKey(string $metaKey): self
    {
        $this->metaKey = $metaKey;

        return $this;
    }

    /**
     * @unreleased
     *
     * @return  string|null
     */
    public function getMetaKey()
    {
        return $this->metaKey;
    }

    /**
     * @unreleased updated to use scoping under the hood, no signature change
     * @since 2.28.0 added types
     * @since 2.10.2
     */
    public function storeAsDonorMeta(bool $storeAsDonorMeta = true): self
    {
        $this->scope = $storeAsDonorMeta
            ? PersistenceScope::donor()
            : PersistenceScope::donation();

        return $this;
    }

    /**
     * @unreleased updated to use scoping under the hood, no signature change
     * @since 2.28.0 added types
     * @since 2.10.2
     */
    public function shouldStoreAsDonorMeta(): bool
    {
        return $this->scope->isDonor();
    }
}
