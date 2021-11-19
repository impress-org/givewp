<?php

global $post;

use Give\Form\Template;
use Give\Helpers\Form\Template as FormTemplateUtils;
use Give\Helpers\Form\Template\Utils\Admin as AdminFormTemplateUtils;

$activatedTemplate = FormTemplateUtils::getActiveID($post->ID);
$registeredTemplates = Give()->templates->getTemplates();
?>
<div class="form_template_options_wrap inner-panel<?php
echo $activatedTemplate ? ' has-activated-template' : ''; ?>">
    <strong class="templates-list-heading"><?php
        _e('Available Form Templates', 'give'); ?></strong>
    <div class="templates-list">
        <?php
        /* @var Template $template */
        foreach ($registeredTemplates as $template) {
            $isActive = $activatedTemplate === $template->getID();

            printf(
                '<div class="template-info %1$s" data-id="%2$s">
							<div class="template-image-container">
								<img class="template-image" src="%3$s"/>
							</div>
							<div class="action">
								<div class="template-name">%4$s <span class="badge">%5$s</span></div>
								<button class="button %7$s">%6$s</button>
							</div>
						</div>',
                $template->getID() . ($isActive ? ' active' : ''),
                $template->getID(),
                $template->getImage(),
                $template->getName(),
                __('active', 'give'),
                $isActive ? __('Deactivate', 'give') : __('Activate', 'give'),
                $isActive ? 'js-template--deactivate' : 'js-template--activate'
            );
        }
        ?>
    </div>

    <div class="form-template-introduction">
        <p>
            <?php
            _e('What Are Form Templates?', 'give'); ?>
        </p>
        <p class="give-field-description form-template-description"><?php
            _e(
                'Form Templates are built-in ways of changing the appearance of a GiveWP form on your site. Each template has a different design, layout, and features. Choose the one that suits your taste and the requirements for your cause. Note: compatibility with add-ons and third-party plugins or themes is not guaranteed. Always thoroughly test your donation forms before going live!',
                'give'
            ); ?></p>

        <div class="form-template-notice give-notice notice notice-success inline">
            <img src="<?php
            echo esc_url(GIVE_PLUGIN_URL . 'assets/dist/images/give-icon-full-circle.svg'); ?>" alt="<?php
            esc_html_e('GiveWP', 'give'); ?>" class="give-logo" style="width:35px;" />
            <p><?php
                esc_html_e('Learn the ins and outs of creating the perfect Donation Form with GiveWP', 'give'); ?></p>
            <a href="http://docs.givewp.com/form-templates/" target="_blank" class="button"><?php
                _e('Learn More', 'give'); ?> <span class="dashicons dashicons-external"></span></a>
        </div>
    </div>

    <div class="form-template-options-introduction">
        <strong>
            <?php
            _e('Form Template Options', 'give'); ?>
        </strong>
        <p class="give-field-description"><?php
            _e(
                'Customize the form template using the options below. See those customizations at any time using the "Preview" button.',
                'give'
            ); ?></p>
    </div>

    <div class="form-template-options">
        <?php
        /* @var Template $template */
        foreach ($registeredTemplates as $template) {
            printf(
                '<div class="template-options %1$s" data-id="%2$s">%3$s</div>',
                $template->getID() . ($activatedTemplate === $template->getID() ? ' active' : ''),
                $template->getID(),
                AdminFormTemplateUtils::renderMetaboxSettings($template)
            );
        }
        ?>
    </div>
</div>
