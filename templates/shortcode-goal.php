<?php
/**
 * This template is used to display the goal with [give_goal]
 */

$form        = new Give_Donate_Form( $form_id );
$goal_option = give_get_meta( $form->ID, '_give_goal_option', true );

//Sanity check - ensure form has pass all condition to show goal.
if (
	( isset( $args['show_goal'] ) && ! filter_var( $args['show_goal'], FILTER_VALIDATE_BOOLEAN ) )
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
$progress = 'donation' !== $goal_format ?
	round( ( $income / $goal ) * 100, 2 ) :
	round( ( $donations_completed / $donations_goal ) * 100, 2 );

$progress = apply_filters( 'give_goal_amount_funded_percentage_output', $progress, $form_id, $form );


/**
 * Filter the give currency.
 *
 * @since 1.8.17
 */
$form_currency = apply_filters( 'give_goal_form_currency', give_get_currency( $form_id ), $form_id );

// Set progress to 100 percentage if income > goal or completed donations > donation goal count.
if ( 'donation' === $goal_format ) {
	$progress_bar_value = $donations_completed >= $donations_goal ? 100 : $progress;
} else {
	$progress_bar_value = $income >= $goal ? 100 : $progress;
}
?>
<div class="give-goal-progress">
	<?php if ( ! empty( $show_text ) ) : ?>
		<div class="raised">
			<?php
			if ( 'amount' === $goal_format ) :

				/**
				 * Filter the income formatting arguments.
				 *
				 * @since 1.8.17
				 */
				$income_format_args = apply_filters( 'give_goal_income_format_args', array( 'sanitize' => false, 'currency' => $form_currency, 'decimal' => false ), $form_id );

				/**
				 * Filter the goal formatting arguments.
				 *
				 * @since 1.8.17
				 */
				$goal_format_args   = apply_filters( 'give_goal_amount_format_args', array( 'sanitize' => false, 'currency' => $form_currency, 'decimal' => false ), $form_id );

				// Get formatted amount.
				$income = give_human_format_large_amount( give_format_amount( $income, $income_format_args ), array( 'currency' => $form_currency ) );
				$goal   = give_human_format_large_amount( give_format_amount( $goal, $goal_format_args ), array( 'currency' => $form_currency ) );

				echo sprintf(
				/* translators: 1: amount of income raised 2: goal target amount. */
					__( '<span class="income">%1$s</span> of <span class="goal-text">%2$s</span> raised', 'give' ),
					give_currency_filter( $income, array( 'form_id' => $form_id ) ),
					give_currency_filter( $goal, array( 'form_id' => $form_id ) )
				);

			elseif ( 'percentage' === $goal_format ) :

				echo sprintf(
				/* translators: %s: percentage of the amount raised compared to the goal target */
					__( '<span class="give-percentage">%s%%</span> funded', 'give' ),
					round( $progress )
				);

			elseif ( 'donation' === $goal_format ) :

				echo sprintf(
				/* translators: 1: total number of donations completed 2: total number of donations set as goal */
					_n(
						'<span class="income">%1$s</span> of <span class="goal-text">%2$s</span> donation',
						'<span class="income">%1$s</span> of <span class="goal-text">%2$s</span> donations',
						$donations_goal,
						'give'
					),
					$donations_completed,
					$donations_goal
				);

			endif;
			?>
		</div>
	<?php endif; ?>


	<?php if ( ! empty( $show_bar ) ) : ?>
		<div class="give-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100"
		     aria-valuenow="<?php echo esc_attr( $progress_bar_value ); ?>">
			<span style="width: <?php echo esc_attr( $progress_bar_value ); ?>%;<?php if ( ! empty( $color ) ) {
				echo 'background-color:' . $color;
			} ?>"></span>
		</div><!-- /.give-progress-bar -->
	<?php endif; ?>

</div><!-- /.goal-progress -->