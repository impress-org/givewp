<!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<title><?php _e( 'Donation Processing...', 'give' ); ?></title>
	</head>
	<body>
		<p style="text-align: center"><?php _e( 'Processing...', 'give' ); ?></p>
		<a style="font-size: 0" id="link" href="<?php echo esc_js( $location ); ?>" target="_parent"><?php _e( 'Link', 'give' ); ?></a>
		<script>
			document.getElementById( 'link' ).click();
		</script>
	</body>
</html>
