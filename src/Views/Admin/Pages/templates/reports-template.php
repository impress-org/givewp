<?php
// Reports page markup
// #reports-app is replaced by React app
?>
<div id="reports-app">
	<div class="wrap give-settings-page">
		<div class="give-settings-header">
			<h1 class="wp-heading-inline">
				Reports
			</h1>
			<div class="givewp-period-selector">
				<div class="group">
					<button>
						Day
					</button>
					<button class="selected">
						Week
					</button>
					<button>
						Month
					</button>
					<button>
						Year
					</button>
				</div>
			</div>
		</div>
		<div class="nav-tab-wrapper give-nav-tab-wrapper" style="height: auto; overflow: visible;">
			<a class="nav-tab nav-tab-active" href="#/">
				Overview
			</a>
			<a class="nav-tab" href="http://givewp.local/wp-admin/edit.php?post_type=give_forms&amp;page=give-reports">
				Legacy Reports Page
			</a>
		</div>
		<div class="givewp-loading-notice">
			<h2>Loading...</h2>
		</div>
	</div>
</div>
