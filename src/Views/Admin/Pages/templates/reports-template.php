<?php
// Reports page markup
// #reports-app is replaced by React app
?>
<div id="reports-app">
	<div class="wrap give-settings-page">
		<div class="give-settings-header">
			<h1 class="wp-heading-inline">
				<?php __( 'Reports', 'give' ); ?>
			</h1>
			<div class="givewp-period-selector">
				<div class="group">
					<button>
						<?php __( 'Day', 'give' ); ?>
					</button>
					<button class="selected">
						<?php __( 'Week', 'give' ); ?>
					</button>
					<button>
						<?php __( 'Month', 'give' ); ?>
					</button>
					<button>
						<?php __( 'Year', 'give' ); ?>
					</button>
				</div>
			</div>
		</div>
		<div class="nav-tab-wrapper give-nav-tab-wrapper" style="height: auto; overflow: visible;">
			<a class="nav-tab nav-tab-active" href="#/">
				<?php __( 'Overview', 'give' ); ?>
			</a>
			<a class="nav-tab" href="http://givewp.local/wp-admin/edit.php?post_type=give_forms&amp;page=give-reports">
				<?php __( 'Legacy Reports Page', 'give' ); ?>
			</a>
		</div>
		<div class="givewp-loading-notice">
			<h2><?php __( 'Loading', 'give' ); ?></h2>
		</div>
	</div>
</div>
