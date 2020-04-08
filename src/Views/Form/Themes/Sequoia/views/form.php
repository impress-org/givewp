<?php
use function \Give\Helpers\Form\Theme\Utils\Frontend\getFormId;

$formId = getFormId();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>  style="margin-top: 0 !important;">
	<head>
		<meta charset="utf-8">
		<title><?php echo apply_filters( 'the_title', get_post_field( 'post_title', $formId ) ); ?></title>
		<?php
		/**
		 * Fire the action hook in header
		 */
		do_action( 'give_embed_head' );
		?>
		<style>
			#loader {
				display: contents;
				position: absolute;
			}
			#loader .give-embed-form {
				height: 734px;
			}
			#form {
				opacity: 0;
				pointer-events: none;
			}

			.sequoia-loaded #loader {
				display: none;
			}

			.sequoia-loaded #form {
				opacity: 1;
				pointer-events: auto;
			}
		</style>
		<script>
			document.addEventListener("DOMContentLoaded", function(event){
				const interval = setInterval(function(){ 
					const height = document.querySelector('.give-form-wrap').offsetHeight;
					document.querySelector('#loader .give-embed-form').setAttribute('style' ,`height: ${height}px;`);
				}, 50);

				const timer = setTimeout(function(){
					loaded();
				}, 1000);

				function loaded () {
					clearTimeout(timer);
					clearInterval(interval);
					document.body.className += ' sequoia-loaded';
				}
			});
		</script>
	</head>
	<body class="give-form-templates">
		<div id="loader">
			<div class="give-embed-form">
				<h1>LOADING!!!!</h1>
			</div>
		</div>
		<div id="form">
		<?php

		// Fetch the Give Form.
		give_get_donation_form( [ 'id' => $formId ] );

		/**
		 * Fire the action hook in footer
		 */
		do_action( 'give_embed_footer' );
		?>
		</div>
	</body>
</html>
