<div class="give-section form-stats">
	<div class="amount-raised">
		<div class="number"><?php echo give_currency_filter(
			give_format_amount(
				$form->get_earnings(),
				array(
					'sanitize' => false,
					'decimal'  => false,
				)
			)
		); ?></div>
		<div class="text">raised</div>
	</div>
	<div class="donation-count">
		<div class="number">
		<?php
		echo give_format_amount(
			$form->get_sales(),
			array(
				'sanitize' => false,
				'decimal'  => false,
			)
		);
		?>
		</div>
		<div class="text">donations</div>
	</div>
	<div class="goal">
		<div class="number">
		<?php
		echo give_currency_filter(
			give_format_amount(
				$form->get_goal(),
				array(
					'sanitize' => false,
					'decimal'  => false,
				)
			)
		);
		?>
		</div>
		<div class="text">goal</div>
	</div>
</div>
