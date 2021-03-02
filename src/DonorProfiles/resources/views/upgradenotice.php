<?php

$setupUrl = admin_url( 'edit.php?post_type=give_forms&page=give-settings' );

?>

<div class="notice give-donor-profile-upgrade-notice is-dismissible hidden" data-give-dismissible="upgrade-donor-profiles-notice-210" >
	<div class="give-donor-profile-upgrade-notice__graphic">
		Donor Profile Graphic
	</div>
	<div class="give-donor-profile-upgrade-notice__copy">
		<div class="give-donor-profile-upgrade-notice__subtitle">
			New in GiveWP 2.10
		</div>
		<div class="give-donor-profile-upgrade-notice__title">
			Donor Profiles
		</div>
		<div class="give-donor-profile-upgrade-notice__body">
			Let donors update their information, review receipts, and mange subscriptions. All in one place.
		</div>
		<a href="#">
			Learn more on the GiveWP blog
		</a>
	</div>
	<div class="give-donor-profile-upgrade-notice__cta">
		<a class="give-donor-profile-upgrade-notice__button" href="<?php echo $setupUrl; ?>">
			Setup Donor Profiles
		</a>
		<a>
			I'll setup Donor Profiles later.
		</a>
	</div>
</div>
