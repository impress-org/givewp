<?php
/**
 * This template is used to display the goal with [give_goal]
 */

$form        = new Give_Donate_Form( $form_id );
$goal_option = give_get_meta( $form->ID, '_give_goal_option', true );

//Sanity check - ensure form has pass all condition to show goal.
if (
	( isset( $args['show_goal'] ) &&  ! filter_var( $args['show_goal'], FILTER_VALIDATE_BOOLEAN ) )
	|| empty( $form->ID )
	|| ( is_singular( 'give_forms' ) && ! give_is_setting_enabled( $goal_option ) )
	|| ! give_is_setting_enabled( $goal_option )
	|| 0 === $form->goal
) {
	return false;
}

$goal_format         = give_get_meta( $form_id, '_give_goal_format', true );
$price               = give_get_meta( $form_id, '_give_set_price', true );
$color               = give_get_meta( $form_id, '_give_goal_color', true );
$show_text           = isset( $args['show_text'] ) ? filter_var( $args['show_text'], FILTER_VALIDATE_BOOLEAN ) : true;
$show_bar            = isset( $args['show_bar'] ) ? filter_var( $args['show_bar'], FILTER_VALIDATE_BOOLEAN ) : true;
$donations_goal      = give_get_meta( $form_id, '_give_number_of_donation_goal', true );
$donations_completed = give_get_form_sales_stats( $form_id );


/**
 * Filter the form income
 *
 * @since 1.8.8
 */
$income = apply_filters( 'give_goal_amount_raised_output', $form->get_earnings(), $form_id, $form );

/**
 * Filter the form
 *
 * @since 1.8.8
 */
$goal = apply_filters( 'give_goal_amount_target_output', $form->goal, $form_id, $form );


/**
 * Filter the goal progress output
 *
 * @since 1.8.8
 */
if ( 'donation' !== $goal_format ) {
	$progress = apply_filters( 'give_goal_amount_funded_percentage_output', round( ( $income / $goal ) * 100, 2 ), $form_id, $form );
} else {
	$progress = apply_filters( 'give_goal_amount_funded_percentage_output', round( ( $donations_completed / $donations_goal ) * 100, 2 ), $form_id, $form );
}

// Set progress to 100 percentage if income > goal.
if ( ( $income >= $goal ) || ( $donations_completed >= $donations_goal ) ) {
	$progress = 100;
}

?>
<div class="give-goal-progress">
	<?php if ( ! empty( $show_text ) ) : ?>
		<div class="raised">
			<?php
			if ( $goal_format === 'amount' ) :

				// Get formatted amount.
				$income = give_human_format_large_amount( give_format_amount( $income, array( 'sanitize' => false ) ) );
				$goal   = give_human_format_large_amount( give_format_amount( $goal, array( 'sanitize' => false ) ) );

				echo sprintf(
				/* translators: 1: amount of income raised 2: goal target ammount */
					__( '%1$s of %2$s raised', 'give' ),
					'<span class="income">' . give_currency_filter( $income ) . '</span>',
					'<span class="goal-text">' . give_currency_filter( $goal ) . '</span>'
				);

			elseif ( $goal_format === 'percentage' ) :

				echo sprintf(
				/* translators: %s: percentage of the amount raised compared to the goal target */
					__( '%s%% funded', 'give' ),
					'<span class="give-percentage">' . round( $progress ) . '</span>'
				);

			elseif ( $goal_format === 'donation' ) :

				echo sprintf(
				/* translators: 1: total number of donations completed 2: total number of donations set as goal */
					__( '%1$s of %2$s donations', 'give' ),
					'<span class="income">' . $donations_completed . '</span>',
					'<span class="goal-text">' . $donations_goal . '</span>'
				);

			endif;
			?>
		</div>
	<?php endif; ?>


	<?php if ( ! empty( $show_bar ) ) : ?>
		<div class="give-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( $progress ); ?>">
			<span style="width: <?php echo esc_attr( $progress ); ?>%;<?php if ( ! empty( $color ) ) {
				echo 'background-color:' . $color;
			} ?>"></span>
		</div><!-- /.give-progress-bar -->
	<?php endif; ?>

</div><!-- /.goal-progress -->