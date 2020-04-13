<?php
/**
 * Payment confirmation view.
 *
 * @since 2.7.0
 */

use Give\Views\IframeView;

$iframeView       = new IframeView();
$paymentGatewayId = give_clean( $_GET['payment-confirmation'] );

ob_start();
?>
	<div id="give-payment-processing">
		<?php
		Give_Notices::print_frontend_notice(
			sprintf(
				/* translators: %s: success page URL */
				__( 'Your donation is processing. This page will reload automatically in 8 seconds. If it does not, click <a href="%s">here</a>.', 'give' ),
				give_get_success_page_uri()
			),
			true,
			'success'
		);
		?>
		<span class="give-loading-animation"></span>
		<script type="text/javascript">
			setTimeout( function() {
				window.location = '<?php echo give_get_success_page_uri(); ?>';
			}, 9000 );
		</script>
	</div>
<?php
echo $iframeView->setTitle( __( 'Donation Processing', 'give' ) )
				->setBody( ob_get_clean() )
				->render();
