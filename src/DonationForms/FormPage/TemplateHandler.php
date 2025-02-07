<?php

namespace Give\DonationForms\FormPage;

use Give\Helpers\Form\Utils;
use WP_Post as Post;

/**
 * @since 3.0.0
 */
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

    /**
     * @since 3.20.0 Add earlier return for archive pages
     * @since 3.0.0
     */
    public function handle($template)
    {
        if (is_archive()) {
            return $template;
        }

        return $this->isNextGenForm()
            ? $this->formPageTemplatePath
            : $template;
    }

    /**
     * @since 3.0.3 Use isV3Form() method instead of 'post_content' to check if the form is built with Visual Builder
     * @since      3.0.0
     */
    protected function isNextGenForm(): bool
    {
        return $this->post
               && Utils::isV3Form($this->post->ID)
               && 'give_forms' === $this->post->post_type;
    }
}
