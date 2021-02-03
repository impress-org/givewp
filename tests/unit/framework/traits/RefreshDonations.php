<?php

trait RefreshDonations {

	protected function refreshDonations() {
		global $wpdb;
        $wpdb->delete( $wpdb->posts, array( 'post_type' => 'give_payment' ) );
	}
} 