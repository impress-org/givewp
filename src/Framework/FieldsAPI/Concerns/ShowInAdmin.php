<?php

namespace Give\Framework\FieldsAPI\Concerns;

/**
 * @unreleased
 */
trait ShowInAdmin
{

    /**
     * @unreleased
     * @var bool
     */
    protected $showInAdmin = false;

    /**
     * @unreleased
     * @return $this
     */
    public function showInAdmin($showInAdmin = true)
    {
        $this->showInAdmin = $showInAdmin;

        return $this;
    }

    /**
     * @unreleased
     * @return bool
     */
    public function shouldShowInAdmin()
    {
        return $this->showInReceipt;
    }
}
