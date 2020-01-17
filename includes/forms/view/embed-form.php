<?php
$title       = 'Testing';
$description = 'Testing description';


$form_id = get_query_var( 'give_form_id' );
$atts    = array(
	'id'            => ! empty( $form_id ) ? absint( $form_id ) : 0,
	'display_style' => 'onpage',
);
$ref_url = home_url() . '?rpid=' . $atts['id'];
?>
<!DOCTYPE html>
<html lang="en" class="give-form-styles" style="margin-top: 0 !important;">
<head>
	<meta charset="utf-8">
	<title><?php echo esc_html( $title ); ?></title>

	<?php wp_head(); ?>
</head>
	<style>
		body{
			max-width: 500px;
			min-width: 301px;
			margin: 0 auto;
		}
	</style>
<body>

<?php
// Fetch the Give Form.
ob_start();
give_get_donation_form( $atts );
echo ob_get_clean();
?>
</body>
</html>
