<?php

namespace Give\Framework\FieldsAPI\Concerns;

/**
 * @unreleased
 */
trait ShowInAdmin
{

    /**
     * @unreleased
     */
    protected $showInAdmin = false;

    /**
     * @unreleased
     */
    public function showInAdmin($showInAdmin = true): self
    {
        $this->showInAdmin = $showInAdmin;

        return $this;
    }

    /**
     * @unreleased
     */
    public function shouldShowInAdmin(): bool
    {
        return $this->showInAdmin;
    }
}
