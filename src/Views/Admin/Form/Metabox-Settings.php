<?php
global $post;

use Give\Form\Template;
use Give\Helpers\Form\Template as FormTemplateUtils;
use Give\Helpers\Form\Template\Utils\Admin as AdminFormTemplateUtils;

$activatedTemplate   = FormTemplateUtils::getActiveID( $post->ID );
$registeredTemplates = Give()->templates->getTemplates();
?>
<div class="form_template_options_wrap inner-panel<?php echo $activatedTemplate ? ' has-activated-template' : ''; ?>">
	<strong class="templates-list-heading"><?php _e( 'Available Form Templates', 'give' ); ?></strong>
	<div class="templates-list">
		<?php
		/* @var Template $template */
		foreach ( $registeredTemplates as $template ) {
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
				$template->getID() . ( $isActive ? ' active' : '' ),
				$template->getID(),
				$template->getImage(),
				$template->getName(),
				__( 'active', 'give' ),
				$isActive ? __( 'Deactivate', 'give' ) : __( 'Activate', 'give' ),
				$isActive ? 'js-template--deactivate' : 'js-template--activate'
			);
		}
		?>
	</div>

	<div class="form-template-introduction">
		<p>
			<?php _e( 'What is a Form Template?', 'give' ); ?>
		</p>
		<p class="give-field-description form-template-description"><?php _e( 'In GiveWP, a form template is a collection of templates and stylesheets used to define the appearance and display of a donation form on your website. Each one comes with a different design, layout and feature. All you need to do is choose the one that suits your taste and requirements for your cause.Compatibility with add-ons and third party plugins depend on the template chosen. Be sure to test your donation form before going live to ensure smooth sailing!', 'give' ); ?></p>

		<div class="form-template-notice give-notice notice notice-success inline">
			<svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12.6363 0.580765C5.65576 0.580765 0 6.23653 0 13.2171C0 20.1976 5.65576 25.8534 12.6363 25.8534C19.6168 25.8534 25.2726 20.1976 25.2726 13.2171C25.2726 6.23653 19.6168 0.580765 12.6363 0.580765ZM12.6363 23.4076C6.98053 23.4076 2.44573 18.8728 2.44573 13.2171C2.44573 7.61225 6.98053 3.0265 12.6363 3.0265C18.2411 3.0265 22.8269 7.61225 22.8269 13.2171C22.8269 18.8728 18.2411 23.4076 12.6363 23.4076ZM18.5978 15.9685C18.0882 15.5609 17.324 15.6118 16.9163 16.1214C15.8463 17.3952 14.2668 18.1595 12.6363 18.1595C10.9549 18.1595 9.37532 17.3952 8.35626 16.1214C7.89769 15.6118 7.13339 15.5609 6.62386 15.9685C6.11434 16.4271 6.01243 17.1914 6.47101 17.7009C7.99959 19.5352 10.2415 20.5543 12.6363 20.5543C14.9801 20.5543 17.222 19.5352 18.7506 17.7009C19.2092 17.1914 19.1583 16.4271 18.5978 15.9685ZM8.56007 12.4018C9.42627 12.4018 10.1906 11.6885 10.1906 10.7713C10.1906 9.90513 9.42627 9.14084 8.56007 9.14084C7.64292 9.14084 6.92958 9.90513 6.92958 10.7713C6.92958 11.6885 7.64292 12.4018 8.56007 12.4018ZM16.7125 9.34465C15.3877 9.34465 13.8592 10.2108 13.6554 11.5356C13.5534 12.0961 14.2158 12.4528 14.6235 12.0451L15.133 11.6375C15.8973 10.9751 17.4768 10.9751 18.2411 11.6375L18.6997 12.0451C19.1583 12.4528 19.8206 12.0961 19.7187 11.5356C19.5149 10.2108 17.9863 9.34465 16.7125 9.34465Z" fill="#66BB6A"/>
			</svg>
			<p>
				<?php _e( 'More templates are coming soon! Let us know what you want to see next.', 'give' ); ?>
			</p>
			<button class="button"><?php _e( 'Take the Survey', 'give' ); ?></button>
		</div>
	</div>

	<div class="form-template-options-introduction">
		<strong>
			<?php _e( 'Form Template Options', 'give' ); ?>
		</strong>
		<p class="give-field-description"><?php _e( 'Customize the appearance of your form template by modifying the options below. You can preview your changes using "Preview button at anytime."', 'give' ); ?></p>
	</div>

	<div class="form-template-options">
		<?php
		/* @var Template $template */
		foreach ( $registeredTemplates as $template ) {
			printf(
				'<div class="template-options %1$s" data-id="%2$s">%3$s</div>',
				$template->getID() . ( $activatedTemplate === $template->getID() ? ' active' : '' ),
				$template->getID(),
				AdminFormTemplateUtils::renderMetaboxSettings( $template )
			);
		}
		?>
	</div>
</div>
