<?php

$pageId = give_get_option( 'donor_profile_page' );

$pageUrl = get_permalink( $pageId );

?>

<div class="notice notice-success is-dismissible">
	<p><?php printf( __( 'Success! Donor Profile page was created. You can <a href="%s">take a look at it here.</a>' ), $pageUrl ); ?></p>
</div>
