<?php

namespace Give\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\ValueObjects\PersistenceScope;

trait HasPersistence
{
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
     * @param string|PersistenceScope $scope
     */
    public function scope($scope): self
    {
        $this->scope = $scope instanceof PersistenceScope ? $scope : new PersistenceScope($scope);

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
     * @unreleased
     */
    public function metaKey(string $metaKey): self
    {
        $this->metaKey = $metaKey;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getMetaKey(): string
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
