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

$form_id     = is_object( $post ) ? $post->ID : 0;
$goal_option = get_post_meta( $form_id, '_give_goal_option', true );
$form        = new Give_Donate_Form( $form_id );
$goal        = $form->goal;
$income      = $form->get_earnings();
$color       = get_post_meta( $form_id, '_give_goal_color', true );


if ( empty( $form->ID ) || $goal_option !== 'yes' ) {
	return false;
}

if ( $goal == 0 ) {
	return;
}

$progress = round( ( $income / $goal ) * 100, 2 );
if ( $income > $goal ) {
	$progress = 100;
}
?>
<div class="goal-progress">
	<div class="raised"><?php echo sprintf( _x( '%s of %s raised', 'give', 'This text displays the amount of income raised compared to the goal.' ), '<span class="income">' . give_currency_filter( give_format_amount( $income ) ) . '</span>', '<span class="goal-text">' . give_currency_filter( give_format_amount( $goal ) ) ) . '</span>'; ?></div>
	<div class="progress-bar">
		<span style="width: <?php echo esc_attr( $progress ); ?>%; <?php if ( ! empty( $color ) ) {
			echo 'background-color:' . $color;
		} ?>"></span>
	</div>
</div><!-- /.goal-progress -->