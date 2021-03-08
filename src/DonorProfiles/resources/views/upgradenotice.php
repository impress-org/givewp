<?php

$setupUrl = add_query_arg(
	[
		'give-generate-donor-profile-page' => '1',
	],
	admin_url( 'edit.php' )
);

?>

<div class="notice give-donor-profile-upgrade-notice is-dismissible hidden" data-give-dismissible="upgrade-donor-profiles-notice-210" >
	<div class="give-donor-profile-upgrade-notice__graphic">
		<img src="<?php echo GIVE_PLUGIN_URL . '/assets/dist/images/admin/donor-dashboard.svg'; ?>"/>
	</div>
	<div class="give-donor-profile-upgrade-notice__copy">
		<div class="give-donor-profile-upgrade-notice__row">
			<div class="give-donor-profile-upgrade-notice__title">
				Introducing the Donor Dashboard
			</div>
			<div class="give-donor-profile-upgrade-notice__badge">
				<i class="fas fa-bell"></i> New in GiveWP 2.10.0
			</div>
		</div>
		<div class="give-donor-profile-upgrade-notice__body">
			The Donor Dashboard provides your donors with a one-stop location to manage all their giving history, profile, and more! Ready to get started? In order to use the new Donor Dashboard, GiveWP needs to create a new page on your site.
		</div>
		<div class="give-donor-profile-upgrade-notice__row">
			<a class="give-donor-profile-upgrade-notice__button" href="<?php echo $setupUrl; ?>">
				Create Donor Dashboard Page
			</a>
			<div class="give-donor-profile-upgrade-notice__pill">
				Want to know more? Learn more about the <a href="#">new Donor Dashboard <i class="fas fa-external-link-alt"></i></a>
			</div>
		</div>
	</div>
	<div class="give-donor-profile-upgrade-notice__cta">
	
	</div>
</div>
