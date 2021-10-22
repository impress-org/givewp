<?php /** @var array $options */ ?>
<div class="give-form-header">
	<h1><?= $options[ 'main_heading' ]; ?></h1>
	<p><?= $options[ 'description' ]; ?></p>
	<?php if ( $options[ 'secure_badge' ] === 'enabled' ): ?>
	<aside class="give-form-secure-badge">
		100% Secure Donation
	</aside>
	<?php endif; ?>
	<aside class="give-form-stats-panel">
		<ul class="give-form-stats-panel-list">
			<li>$0 raised</li>
			<li>50 donations</li>
			<li>$10,000 goal</li>
			<li class="give-form-goal-progress">
				<meter class="give-form-goal-progress-meter" min="0" max="1000000" value="200000">$20,000 of $1,000,000 goal</meter>
			</li>
		</ul>
	</aside>
</div>
