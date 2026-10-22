<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables; this file is included inside a function, so they are not globals.

/**
 * Displays onboarding message in the event of an empty list table.
 *
 * @since TBD Print the content without passing variables to the translation functions.
 *
 * Available Variables:
 *
 *    array $content {
 *        string $image_url
 *        string $image_alt
 *        string $heading
 *        string $message
 *        string $cta_text
 *        string $cta_link
 *        string $help
 *    }
 */
?>

<div class="give-blank-slate">
	<?php if ( ! empty( $content['image_url'] ) ) : ?>
		<img class="give-blank-slate__image" src="<?php echo esc_url( $content['image_url'] ); ?>" alt="<?php echo esc_attr( $content['image_alt'] ); ?>">
	<?php endif; ?>

	<?php if ( ! empty( $content['heading'] ) ) : ?>
		<h2 class="give-blank-slate__heading"><?php echo esc_html( $content['heading'] ); ?></h2>
	<?php endif; ?>

	<?php if ( ! empty( $content['message'] ) ) : ?>
		<p class="give-blank-slate__message"><?php echo esc_html( $content['message'] ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $content['cta_text'] ) && ! empty( $content['cta_link'] ) ) : ?>
		<a class="give-blank-slate__cta button button-primary" href="<?php echo esc_url( $content['cta_link'] ); ?>"><?php echo esc_html( $content['cta_text'] ); ?></a>
	<?php endif; ?>

	<?php if ( ! empty( $content['help'] ) ) : ?>
		<p class="give-blank-slate__help">
			<?php
			$allowed_html = array(
				'a'      => array(
					'href'   => array(),
					'title'  => array(),
					'target' => array(),
				),
				'em'     => array(),
				'strong' => array(),
				'code'   => array(),
			);

			echo wp_kses( $content['help'], $allowed_html );
			?>
		</p>
	<?php endif; ?>
</div>
