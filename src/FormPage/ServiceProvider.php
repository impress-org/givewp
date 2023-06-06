<?php

namespace Give\FormPage;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /*
     * @inheritdoc
     */
    public function register()
    {
        give()->singleton(TemplateHandler::class, function () {
            global $post;

            return new TemplateHandler(
                $post,
                plugin_dir_path(__FILE__) . 'templates/form-single.php'
            );
        });
    }

    /*
     * @inheritdoc
     */
    public function boot()
    {
        Hooks::addFilter('template_include', TemplateHandler::class, 'handle', 11);
    }
}
