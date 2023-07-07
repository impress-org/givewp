<?php

namespace Give\Framework\FieldsAPI\Concerns;

/**
 * @since 2.27.3
 */
trait ShowInAdmin
{

    /**
     * @since 2.27.3
     */
    protected $showInAdmin = false;

    /**
     * @since 2.27.3
     */
    public function showInAdmin($showInAdmin = true): self
    {
        $this->showInAdmin = $showInAdmin;

        return $this;
    }

    /**
     * @since 2.27.3
     */
    public function shouldShowInAdmin(): bool
    {
        return $this->showInAdmin;
    }
}
