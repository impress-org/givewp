<?php

$pageId = give_get_option('donor_dashboard_page');

$pageUrl = get_permalink($pageId);

?>

<div class="notice notice-success is-dismissible">
    <p><?php
        printf(
            __('Success! Donor Dashboard page was created. You can <a href="%s">take a look at it here.</a>', 'give'),
            $pageUrl
        ); ?></p>
</div>
