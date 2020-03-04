<div class="form_theme_options_wrap inner-panel">
	<strong class="themes-list-heading"><?php _e( 'Available Form Themes', 'give' ); ?></strong>
	<div class="themes-list">
		<?php
		/* @var \Give\Form\Theme $theme */
		foreach ( Give\Form\Themes::getRegisterThemes() as $theme ) {
			printf(
				'<div class="theme-info %1$s" data-id="%1$s">
							<div class="image-placeholder">%2$s</div>
							<div class="action">
								<strong>%3$s <span class="badge">%5$s</span></strong>
								<button class="button js-theme--activate">%4$s</button>
							</div>
						</div>',
				$theme->getID(),
				$theme->getImage(),
				$theme->geName(),
				__( 'Activate', 'give' ),
				__( 'active', 'give' )
			);
		}
		?>
	</div>

	<div class="form-theme-introduction">
		<strong>
			<?php _e( 'What is a Form Theme', 'give' ); ?>
		</strong>
		<p><?php _e( 'In GiveWP, a form theme is a collection of templates and stylesheets used to define then appearance and display of a donation form on your website. Each one comes with a different design, layout and feature. All you need to do is choose the one that suits your taste and requirements for your cause.Compatibility with add-ons and third party plugins depend on the theme chosen. Be sure to test your donation form before going live to ensure smooth sailing!', 'give' ); ?></p>

		<div class="give-notice notice notice-success inline">
			<p>
				<?php _e( 'More themes are coming soon! Let us know what you want to see next', 'give' ); ?>
				<button class="button"><?php _e( 'Take the Survey', 'give' ); ?></button>
			</p>
		</div>
	</div>

	<div class="form-theme-options-introduction">
		<strong>
			<?php _e( 'Form Theme Options', 'give' ); ?>
		</strong>
		<p><?php _e( 'Customize the appearance of your form theme by modifying the options below. You can preview your changes using "Preview button at anytime."', 'give' ); ?></p>
	</div>

	<div class="form-theme-options">
		<?php
		/* @var \Give\Form\Theme $theme */
		foreach ( Give\Form\Themes::getRegisterThemes() as $theme ) {
			printf(
				'<div class="theme-options %1$s" data-id="%1$s">
							Showing %2$s Theme Options
						</div>',
				$theme->getID(),
				$theme->geName()
			);
		}
		?>
	</div>
</div>
