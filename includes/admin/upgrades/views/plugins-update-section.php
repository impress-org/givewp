<?php
/* @var Give_Updates $give_updates */
$plugins = $give_updates->get_updates( 'plugin' );
if ( empty( $plugins ) ) {
	return;
}

ob_start();
foreach ( $plugins as $plugin_data ) {
	$plugin_name = $plugin_data['Name'];
	$author_name = $plugin_data['Author'];

	// Link the plugin name to the plugin URL if available.
	if ( ! empty( $plugin_data['PluginURI'] ) ) {
		$plugin_name = sprintf(
			'<a href="%s" title="%s">%s</a> (%s)',
			esc_url( $plugin_data['PluginURI'] ),
			esc_attr__( 'Visit plugin homepage', 'give' ),
			$plugin_name,
			esc_html( $plugin_data['Version'] )
		);
	}

	// Link the author name to the author URL if available.
	if ( ! empty( $plugin_data['AuthorURI'] ) ) {
		$author_name = sprintf(
			'<a href="%s" title="%s">%s</a>',
			esc_url( $plugin_data['AuthorURI'] ),
			esc_attr__( 'Visit author homepage', 'give' ),
			$author_name
		);
	}
	?>
	<tr>
		<td><?php echo wp_kses( $plugin_name, wp_kses_allowed_html( 'post' ) ); ?></td>
		<td>
			<?php
			echo true === $plugin_data['License']
				? sprintf(
					'<span class="dashicons dashicons-yes"></span>%s',
					__( 'Licensed', 'give' )
				)
				: sprintf(
					'<span data-tooltip="%s"><span class="dashicons dashicons-no-alt"></span>%s</span>',
					__( 'Unlicensed add-ons cannot be updated. Please purchase or renew a valid license.', 'give' ),
					__( 'Unlicensed', 'give' )
				);

			echo sprintf(
				' &ndash; %s &ndash; %s',
				sprintf( _x( 'by %s', 'by author', 'give' ), wp_kses( $author_name, wp_kses_allowed_html( 'post' ) ) ),
				sprintf( __( '(Latest Version: %s)' ), $plugin_data['update']->new_version )
			);
			?>
		</td>
	</tr>
	<?php
}
echo sprintf(
	'<table><tbody>%s</tbody></table>',
	ob_get_clean()
);
?>