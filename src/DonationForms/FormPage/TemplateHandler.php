<?php

namespace Give\DonationForms\FormPage;

use Give\Helpers\Form\Utils;
use WP_Post as Post;

class TemplateHandler
{
    /**
     * @var Post|null
     */
    private $post;

    /**
     * @var string
     */
    private $formPageTemplatePath;

    public function __construct( $post, string $formPageTemplatePath )
    {
        $this->post = $post;
        $this->formPageTemplatePath = $formPageTemplatePath;
    }

    public function handle($template)
    {
        return $this->isNextGenForm()
            ? $this->formPageTemplatePath
            : $template;
    }

    /**
     * @unreleased Use isV3Form() method instead of 'post_content' to check if the form is built with Visual Builder
     */
    protected function isNextGenForm(): bool
    {
        return $this->post
               && Utils::isV3Form($this->post->ID)
               && 'give_forms' === $this->post->post_type;
    }
}
