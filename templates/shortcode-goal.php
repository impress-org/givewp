<?php
/**
 * This template is used to display the goal with [give_goal]
 */

$form        = new Give_Donate_Form( $form_id );
$goal_option = give_get_meta( $form->ID, '_give_goal_option', true );

// Sanity check - ensure form has pass all condition to show goal.
if ( ( isset( $args['show_goal'] ) && ! filter_var( $args['show_goal'], FILTER_VALIDATE_BOOLEAN ) )
	 || empty( $form->ID )
	 || ( is_singular( 'give_forms' ) && ! give_is_setting_enabled( $goal_option ) )
	 || ! give_is_setting_enabled( $goal_option ) || 0 === $form->goal ) {
	return false;
}

$goal_progress_stats = give_goal_progress_stats( $form );
$goal_format         = $goal_progress_stats['format'];
$color               = give_get_meta( $form_id, '_give_goal_color', true );
$show_text           = isset( $args['show_text'] ) ? filter_var( $args['show_text'], FILTER_VALIDATE_BOOLEAN ) : true;
$show_bar            = isset( $args['show_bar'] ) ? filter_var( $args['show_bar'], FILTER_VALIDATE_BOOLEAN ) : true;

$income = $form->get_earnings();
$goal   = $goal_progress_stats['raw_goal'];

switch ( $goal_format ) {

	case 'donation':
		$progress           = $goal ? round( ( $income / $goal ) * 100, 2 ) : 0;
		$progress_bar_value = $income >= $goal ? 100 : $progress;
		break;

	case 'donors':
		$progress_bar_value = $goal ? round( ( $income / $goal ) * 100, 2 ) : 0;
		$progress           = $progress_bar_value;
		break;

	case 'percentage':
		$progress           = $goal ? round( ( $income / $goal ) * 100, 2 ) : 0;
		$progress_bar_value = $income >= $goal ? 100 : $progress;
		break;

	default:
		$progress           = $goal ? round( ( $income / $goal ) * 100, 2 ) : 0;
		$progress_bar_value = $income >= $goal ? 100 : $progress;
		break;

}

/**
 * Filter the goal progress output
 *
 * @since 1.8.8
 */
$progress = apply_filters( 'give_goal_amount_funded_percentage_output', $progress, $form_id, $form );
?>
<div class="give-goal-progress">
	<?php if ( ! empty( $show_text ) ) : ?>
		<div class="raised">
			<?php
			if ( 'amount' === $goal_format ) :

				/**
				 * Filter the give currency.
				 *
				 * @since 1.8.17
				 */
				$form_currency = apply_filters( 'give_goal_form_currency', give_get_currency( $form_id ), $form_id );

				/**
				 * Filter the income formatting arguments.
				 *
				 * @since 1.8.17
				 */
				$income_format_args = apply_filters(
					'give_goal_income_format_args',
					array(
						'sanitize' => false,
						'currency' => $form_currency,
						'decimal'  => false,
					),
					$form_id
				);

				/**
				 * Filter the goal formatting arguments.
				 *
				 * @since 1.8.17
				 */
				$goal_format_args = apply_filters(
					'give_goal_amount_format_args',
					array(
						'sanitize' => false,
						'currency' => $form_currency,
						'decimal'  => false,
					),
					$form_id
				);

				/**
				 * This filter will be used to convert the goal amounts to different currencies.
				 *
				 * @since 2.5.4
				 *
				 * @param array $amounts List of goal amounts.
				 * @param int   $form_id Donation Form ID.
				 */
				$goal_amounts = apply_filters(
					'give_goal_amounts',
					array(
						$form_currency => $goal,
					),
					$form_id
				);

				/**
				 * This filter will be used to convert the income amounts to different currencies.
				 *
				 * @since 2.5.4
				 *
				 * @param array $amounts List of goal amounts.
				 * @param int   $form_id Donation Form ID.
				 */
				$income_amounts = apply_filters(
					'give_goal_raised_amounts',
					array(
						$form_currency => $income,
					),
					$form_id
				);

				// Get human readable donation amount.
				$income = give_human_format_large_amount( give_format_amount( $income, $income_format_args ), array( 'currency' => $form_currency ) );
				$goal   = give_human_format_large_amount( give_format_amount( $goal, $goal_format_args ), array( 'currency' => $form_currency ) );

				// Format the human readable donation amount.
				$formatted_income = give_currency_filter(
					$income,
					array(
						'form_id' => $form_id,
					)
				);

				$formatted_goal = give_currency_filter(
					$goal,
					array(
						'form_id' => $form_id,
					)
				);

				echo sprintf(
					/* translators: 1: amount of income raised 2: goal target amount. */
					__( '<div class="raised-details">
                              <div class="income">
                                   <span class="amount"  data-amounts="%1$s">%2$s</span>
                                   <span> Raised </span>
                              </div>
                              <div class="goal-text">
                                   <span class="amount" data-amounts="%3$s">%4$s</span>
                                   <span> Goal </span>
                              </div>
                         </div>', 'give' ),
					esc_attr( wp_json_encode( $income_amounts, JSON_PRETTY_PRINT ) ),
					esc_attr( $formatted_income ),
					esc_attr( wp_json_encode( $goal_amounts, JSON_PRETTY_PRINT ) ),
					esc_attr( $formatted_goal )
				);

			elseif ( 'percentage' === $goal_format ) :

				echo sprintf( /* translators: %s: percentage of the amount raised compared to the goal target */
					__( '<div class="raised-details">
                              <div class="income">
                                   <span class="give-percentage amount">%s%%</span>
                                   <span> Raised </span>
                              </div>
                              <div class="goal-text">
                                   <span> 100&#37; </span>
                                   <span> Goal </span>
                              </div>
                         </div>', 'give' ),
					round( $progress )
				);

			elseif ( 'donation' === $goal_format ) :

				echo sprintf( /* translators: 1: total number of donations completed 2: total number of donations set as goal */
					_n(
						'<div class="raised-details">
                                      <div class="income">
                                           <span class="amount">%1$s</span>
                                           <span> Donations Raised </span>
                                      </div>
                                      <div class="goal-text">
                                           <span class="amount">%2$s</span>
                                           <span> Donation Goal </span>
                                      </div>
                                </div>',
						'<div class="raised-details">
                                      <div class="income">
                                           <span class="amount">%1$s</span>
                                           <span> Donations Raised </span>
                                      </div>
                                      <div class="goal-text">
                                           <span class="amount">%2$s</span>
                                           <span> Donation Goal </span>
                                      </div>
                                </div>',
						$goal,
						'give'
					),
					give_format_amount( $income, array( 'decimal' => false ) ),
					give_format_amount( $goal, array( 'decimal' => false ) )
				);

			elseif ( 'donors' === $goal_format ) :

				echo sprintf( /* translators: 1: total number of donors completed 2: total number of donors set as goal */
					_n(
						'<div class="raised-details">
                                      <div class="income">
                                           <span class="amount">%1$s</span>
                                           <span> Donors </span>
                                      </div>
                                      <div class="goal-text">
                                           <span class="amount">%2$s</span>
                                           <span> Goal </span>
                                      </div>
                                </div>',
						'<div class="raised-details">
                                      <div class="income">
                                           <span class="amount">%1$s</span>
                                           <span> Donors </span>
                                      </div>
                                      <div class="goal-text">
                                           <span class="amount">%2$s</span>
                                           <span> Goal </span>
                                      </div>
                                </div>',
						$goal,
						'give'
					),
					give_format_amount( $income, array( 'decimal' => false ) ),
					give_format_amount( $goal, array( 'decimal' => false ) )
				);

			endif;
			?>
		</div>
	<?php endif; ?>


	<?php if ( ! empty( $show_bar ) ) :
        $style = "width:{$progress_bar_value}%;";

        if ( ! empty($color)) {
            $style .= ";background: linear-gradient(180deg, {$color} 0%, {$color} 100%), linear-gradient(180deg, #fff 0%, #ccc 100%); background-blend-mode: multiply;";
        }
        ?>
        <div class="progress-bar">
            <div class="give-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( $progress_bar_value ); ?>">
                <span style="<?php echo $style; ?>"></span>
            </div>
		</div>
	<?php endif; ?>

</div><!-- /.goal-progress -->
