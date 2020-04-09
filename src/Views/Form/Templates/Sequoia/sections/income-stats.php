<?php
/**
 * @var int $formId
 */
if ( $form->has_goal() ) : ?>
	<?php
	$goalStats = give_goal_progress_stats( $formId );

	// Setup default raised value
	$raised = give_currency_filter(
		give_format_amount(
			$form->get_earnings(),
			[
				'sanitize' => false,
				'decimal'  => false,
			]
		)
	);

	// Setup default count value
	$count = $form->get_sales();

	// Setup default count label
	$countLabel = _n( 'donation', 'donations', $count, 'give' );

	// Setup default goal value
	$goal = give_currency_filter(
		give_format_amount(
			$form->get_goal(),
			[
				'sanitize' => false,
				'decimal'  => false,
			]
		)
	);

	// Change values and labels based on goal format
	switch ( $goalStats['format'] ) {
		case 'percentage': {
			$raised = "{$goalStats['progress']}%";
			break;
		}
		case 'donation': {
			$count = $goalStats['actual'];
			$goal  = $goalStats['goal'];
			break;
		}
		case 'donors': {
			$count      = $goalStats['actual'];
			$countLabel = _n( 'donor', 'donors', $count, 'give' );
			$goal       = $goalStats['goal'];
			break;
		}
	}
	?>
<div class="income-stats">
	<div class="raised">
		<div class="number">
			<?php echo $raised; ?>
		</div>
		<div class="text"><?php _e( 'raised', 'give' ); ?></div>
	</div>
	<div class="count">
		<div class="number">
			<?php echo $count; ?>
		</div>
		<div class="text"><?php echo $countLabel; ?></div>
	</div>
	<div class="goal">
		<div class="number">
		<?php echo $goal; ?>
		</div>
		<div class="text"><?php _e( 'goal', 'give' ); ?></div>
	</div>
</div>
<?php endif; ?>
