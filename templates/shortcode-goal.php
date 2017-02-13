<?php
/**
 * This template is used to display the goal with [give_goal]
 */

$goal_option = get_post_meta( $form_id, '_give_goal_option', true );
$form        = new Give_Donate_Form( $form_id );
$goal        = $form->goal;
$goal_format = get_post_meta( $form_id, '_give_goal_format', true );
$income      = $form->get_earnings();
$color       = get_post_meta( $form_id, '_give_goal_color', true );
$show_text   = isset( $args['show_text'] ) ? filter_var( $args['show_text'], FILTER_VALIDATE_BOOLEAN ) : true;
$show_bar    = isset( $args['show_bar'] ) ? filter_var( $args['show_bar'], FILTER_VALIDATE_BOOLEAN ) : true;

//Sanity check - respect shortcode args
if ( isset( $args['show_goal'] ) && $args['show_goal'] === false ) {
	return false;
}

//Sanity check - ensure form has goal set to output
if ( empty( $form->ID )
	|| ( is_singular( 'give_forms' ) && ! give_is_setting_enabled( $goal_option ) )
	|| ! give_is_setting_enabled( $goal_option )
	|| $goal == 0
) {
	//not this form, bail
	return false;
}

$progress = round( ( $income / $goal ) * 100, 2 );

if ( $income >= $goal ) {
	$progress = 100;
}

?>
<div class="give-goal-progress">
	<?php if ( ! empty( $show_text ) ) : ?>
		<div class="raised">
			<?php
			if ( $goal_format !== 'percentage' ) :

				// Get formatted amount.
				$income = give_human_format_large_amount( give_format_amount( $income ) );
				$goal = give_human_format_large_amount( give_format_amount( $goal ) );

				echo sprintf(
				/* translators: 1: amount of income raised 2: goal target ammount */
					__( '%1$s of %2$s raised', 'give' ),
					'<span class="income">' . apply_filters( 'give_goal_amount_raised_output', give_currency_filter( $income ) ) . '</span>',
					'<span class="goal-text">' . apply_filters( 'give_goal_amount_target_output', give_currency_filter( $goal ) ) . '</span>'
				);


			elseif ( $goal_format == 'percentage' ) :

				echo sprintf(
				/* translators: %s: percentage of the amount raised compared to the goal target */
					__( '%s%% funded', 'give' ),
					'<span class="give-percentage">' . apply_filters( 'give_goal_amount_funded_percentage_output', round( $progress ) ) . '</span>'
				);

			endif;
			?>
		</div>
	<?php endif; ?>


	<?php if ( ! empty( $show_bar ) ) : ?>
		<div class="give-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php  echo esc_attr( $progress ); ?>">
			<span style="width: <?php  echo esc_attr( $progress ); ?>%;<?php if ( ! empty( $color ) )  echo 'background-color:' . $color; ?>"></span>
		</div><!-- /.give-progress-bar -->
	<?php endif; ?>

</div><!-- /.goal-progress -->