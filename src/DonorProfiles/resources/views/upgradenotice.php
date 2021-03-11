<?php

$setupUrl = add_query_arg(
	[
		'give-generate-donor-dashboard-page' => '1',
	],
	admin_url( 'edit.php' )
);

?>

<div class="notice give-donor-dashboard-upgrade-notice is-dismissible hidden" data-give-dismissible="upgrade-donor-dashboards-notice-210" >
	<div class="give-donor-dashboard-upgrade-notice__graphic">
		<img src="<?php echo GIVE_PLUGIN_URL . '/assets/dist/images/admin/donor-dashboard.svg'; ?>"/>
	</div>
	<div class="give-donor-dashboard-upgrade-notice__copy">
		<div class="give-donor-dashboard-upgrade-notice__row">
			<div class="give-donor-dashboard-upgrade-notice__title">
				<?php _e( 'Introducing the Donor Dashboard', 'give' ); ?>
			</div>
			<div class="give-donor-dashboard-upgrade-notice__badge">
				<i class="fas fa-bell"></i> <?php _e( 'New in GiveWP 2.10.0', 'give' ); ?>
			</div>
		</div>
		<div class="give-donor-dashboard-upgrade-notice__body">
			<?php _e( 'The Donor Dashboard provides your donors with a one-stop location to manage all their giving history, profile, and more! Ready to get started? In order to use the new Donor Dashboard, GiveWP needs to create a new page on your site.', 'give' ); ?>
		</div>
		<div class="give-donor-dashboard-upgrade-notice__row">
			<a class="give-donor-dashboard-upgrade-notice__button" href="<?php echo $setupUrl; ?>">
				<?php _e( 'Create Donor Dashboard Page', 'give' ); ?>
			</a>
			<div class="give-donor-dashboard-upgrade-notice__pill">
				<?php printf( __( 'Want to know more? Learn more about the <a href="%s" target="_blank">new Donor Dashboard <i class="fas fa-external-link-alt"></i></a>', 'give' ), 'http://docs.givewp.com/donor-dashboard' ); ?>
			</div>
		</div>
		<a class="give-donor-dashboard-upgrade-notice__dismiss-link">
			<?php _e( 'I\'ll setup Donor Dashboards later', 'give' ); ?>
		</a>
	</div>
</div>
