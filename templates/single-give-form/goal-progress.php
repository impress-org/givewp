<?php
/**
 * Single Give Form Goal Progress
 *
 * @package       Give/Templates
 * @version       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post;

$form_id = is_object( $post ) ? $post->ID : 0;

$form = new Give_Donate_Form( $form_id );

if ( empty( $form->ID ) ) {
	return false;
}

$goal = $form->goal;

if ( $goal == 0 ) {
	return;
}

$income   = $form->get_earnings();
$progress = round( ( $income / $goal ) * 100, 2 );
if ( $income > $goal ) {
	$progress = 100;
}
?>
<div class="goal-progress">
	<div class="raised"><?php echo sprintf( __( '<span class="income">%s</span> of %s raised', 'give' ), give_currency_filter( give_format_amount( $income ) ), give_currency_filter( give_format_amount( $goal ) ) ); ?></div>
	<div class="progress-bar">
		<span style="width: <?php echo esc_attr( $progress ); ?>%"></span>
	</div>
</div><!-- /.goal-progress -->