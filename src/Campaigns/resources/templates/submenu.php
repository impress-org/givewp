<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&display=swap" rel="stylesheet"> 

<div class="wrap give-campaigns-page">

	<h1 class="wp-heading-inline">
		<?php echo __( 'Campaigns', 'give' ); ?>
	</h1>

	<!-- <a href="#" class="button button-secondary"> Add New</a> -->

	<hr class="wp-header-end">

	<div id="root"></div>

	<?php
	$data = give_campaings_get_aggregate_total_query();
	?>

	<p>
	A total of <?php echo $data->total; ?> raised via <?php echo $data->count; ?> donations, with an average donation of <?php echo round( $data->average, 2 ); ?>.
	</p>
</div>
