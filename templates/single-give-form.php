<?php
/**
 * The Template for displaying all single Give Forms.
 *
 * Override this template by copying it to yourtheme/give/single-give-forms.php
 *
 * @package       Give/Templates
 * @version       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
get_header();  ?>

<?php
/**
 * give_before_main_content hook
 *
 * @hooked give_output_content_wrapper - 10 (outputs opening divs for the content)
 */
do_action( 'give_before_main_content' );
?>

<?php while ( have_posts() ) : the_post(); ?>

	<?php give_get_template_part( 'single-give-form/content', 'single-give-form' ); ?>

<?php endwhile; // end of the loop. ?>

<?php
/**
 * give_after_main_content hook
 *
 * @hooked give_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'give_after_main_content' );
?>

<?php
/**
 * give_sidebar hook
 *
 * @hooked give_get_sidebar - 10
 */
do_action( 'give_sidebar' );
?>

<?php get_footer(); ?>
