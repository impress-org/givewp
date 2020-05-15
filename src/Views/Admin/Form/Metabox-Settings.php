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
		<p class="give-field-description form-template-description"><?php _e( 'In GiveWP, a form template is a collection of templates and stylesheets used to define then appearance and display of a donation form on your website. Each one comes with a different design, layout and feature. All you need to do is choose the one that suits your taste and requirements for your cause.Compatibility with add-ons and third party plugins depend on the template chosen. Be sure to test your donation form before going live to ensure smooth sailing!', 'give' ); ?></p>

		<div class="form-template-notice give-notice notice notice-success inline">
			<p>
				<?php _e( 'More templates are coming soon!', 'give' ); ?><br>
				<?php _e( 'Let us know what you want to see next', 'give' ); ?>
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
