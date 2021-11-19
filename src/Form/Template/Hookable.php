<?php

namespace Give\Form\Template;

/**
 * Interface Hookable
 *
 * @package Give\Form\Template
 * @since 2.7.0
 */
interface Hookable
{

    /**
     * Load WordPress hooks
     *
     * @since 2.7.0
     * @return mixed
     */
    public function loadHooks();
}
