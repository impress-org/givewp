<?php
/**
 * @var int $formId
 */
if ( $form->has_goal() ) : ?>
	<?php
	$color     = give_get_meta( $formId, '_give_goal_color', true );
	$goalStats = give_goal_progress_stats( $formId );

	$style = "width:{$goalStats['progress']}%;";
	if ( ! empty( $color ) ) {
		$style .= ";background: linear-gradient(180deg, {$color} 0%, {$color} 100%), linear-gradient(180deg, #fff 0%, #ccc 100%); background-blend-mode: multiply;";
	}
	?>
	<div class="progress-bar">
		<div class="give-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo $goalStats['progress']; ?>">
			<span style="<?php echo $style; ?>"></span>
		</div><!-- /.give-progress-bar -->
	</div>
<?php endif; ?>
