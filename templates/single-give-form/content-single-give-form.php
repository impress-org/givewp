<?php
/**
 * The template for displaying product content in the single-give-form.php template
 *
 * Override this template by copying it to yourtheme/give/single-give-form/content-single-give-form.php
 *
 * @package       Give/Templates
 * @version       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<?php
/**
 * give_before_single_product hook
 *
 * @hooked wc_print_notices - 10
 */
do_action( 'give_before_single_form' );

if ( post_password_required() ) {
	echo get_the_password_form();
	return;
}
?>

	<div id="give-form-<?php the_ID(); ?>-content" <?php post_class(); ?>>

		<?php
		/**
		 * give_before_single_product_summary hook
		 *
		 * @hooked give_show_product_images - 10
		 */
		do_action( 'give_before_single_form_summary' );
		?>

		<div class="<?php echo apply_filters( 'give_forms_single_summary_classes', 'summary entry-summary' ); ?>">

			<?php
			/**
			 * give_single_form_summary hook
			 *
			 * @hooked give_template_single_title - 5
			 * @hooked give_get_donation_form - 10
			 */
			do_action( 'give_single_form_summary' );
			?>

		</div>
		<!-- .summary -->

		<?php
		/**
		 * give_after_single_form_summary hook
		 */
		do_action( 'give_after_single_form_summary' );
		?>


	</div><!-- #give-form-<?php the_ID(); ?> -->

<?php do_action( 'give_after_single_form' ); ?>