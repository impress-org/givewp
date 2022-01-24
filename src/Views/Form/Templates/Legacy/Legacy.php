<?php

namespace Give\Views\Form\Templates\Legacy;

use Give\Form\Template;

class Legacy extends Template
{
    /**
     * @inheritDoc
     */
    public function getID()
    {
        return 'legacy';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return __('Legacy Form', 'give');
    }

    /**
     * @inheritDoc
     */
    public function getImage()
    {
        return GIVE_PLUGIN_URL . 'assets/dist/images/admin/template-preview-legacy.png';
    }

    /**
     * @inheritDoc
     */
    public function getOptionsConfig()
    {
        return require 'optionConfig.php';
    }
}
