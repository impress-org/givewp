[1mdiff --git a/includes/misc-functions.php b/includes/misc-functions.php[m
[1mindex 138ac224..54376a51 100644[m
[1m--- a/includes/misc-functions.php[m
[1m+++ b/includes/misc-functions.php[m
[36m@@ -1300,8 +1300,9 @@[m [mfunction give_import_page_url( $parameter = array() ) {[m
 [m
 [m
 function give_save_import_donation_to_db( $raw_key, $row_data, $main_key = array(), $import_setting = array() ) {[m
[31m-	$data     = array_combine( $raw_key, $row_data );[m
[31m-	$price_id = false;[m
[32m+[m	[32m$data                          = array_combine( $raw_key, $row_data );[m
[32m+[m	[32m$price_id                      = false;[m
[32m+[m	[32m$customer_id                   = 0;[m
 	$import_setting['create_user'] = ( isset( $import_setting['create_user'] ) ? $import_setting['create_user'] : 1 );[m
 [m
 	$data = (array) apply_filters( 'give_save_import_donation_to_db', $data );[m
[36m@@ -1313,7 +1314,9 @@[m [mfunction give_save_import_donation_to_db( $raw_key, $row_data, $main_key = array[m
 	// Here come the login function.[m
 	$donor_data = give_import_get_user_from_csv( $data, $import_setting );[m
 	if ( ! empty( $donor_data->id ) ) {[m
[31m-		$customer_id = $donor_data->id;[m
[32m+[m		[32mif ( ! empty( $donor_data->user_id ) ) {[m
[32m+[m			[32m$customer_id = $donor_data->user_id;[m
[32m+[m		[32m}[m
 	} else {[m
 		return false;[m
 	}[m
[36m@@ -1337,14 +1340,15 @@[m [mfunction give_save_import_donation_to_db( $raw_key, $row_data, $main_key = array[m
 [m
 	//Create payment_data array[m
 	$payment_data = array([m
[32m+[m		[32m'donor_id'        => $donor_data->id,[m
 		'price'           => $data['amount'],[m
 		'status'          => ( isset( $data['post_status'] ) ? $data['post_status'] : 'publish' ),[m
 		'currency'        => give_get_currency(),[m
 		'user_info'       => array([m
 			'id'         => $customer_id,[m
 			'email'      => ( isset( $data['email'] ) ? $data['email'] : ( isset( $donor_data->email ) ? $donor_data->email : false ) ),[m
[31m-			'first_name' => ( isset( $data['first_name'] ) ? $data['first_name'] : ( ( $first_name = get_user_meta( $customer_id, 'first_name', true ) ) ? $first_name : $donor_data->name ) ),[m
[31m-			'last_name'  => ( isset( $data['last_name'] ) ? $data['last_name'] : ( ( $last_name = get_user_meta( $customer_id, 'last_name', true ) ) ? $last_name : $donor_data->name ) ),[m
[32m+[m			[32m'first_name' => ( isset( $data['first_name'] ) ? $data['first_name'] : ( ! empty( $customer_id ) && ( $first_name = get_user_meta( $customer_id, 'first_name', true ) ) ? $first_name : $donor_data->name ) ),[m
[32m+[m			[32m'last_name'  => ( isset( $data['last_name'] ) ? $data['last_name'] : ( ! empty( $customer_id ) && ( $last_name = get_user_meta( $customer_id, 'last_name', true ) ) ? $last_name : $donor_data->name ) ),[m
 			'address'    => $address,[m
 		),[m
 		'gateway'         => ( ! empty( $data['gateway'] ) && 'offline' != strtolower( $data['gateway'] ) ? strtolower( $data['gateway'] ) : 'manual' ),[m
[36m@@ -1608,8 +1612,8 @@[m [mfunction give_import_donation_form_options() {[m
  * @return bool|false|WP_User[m
  */[m
 function give_import_get_user_from_csv( $data, $import_setting = array() ) {[m
[31m-	$report    = give_import_donation_report();[m
[31m-	$donor_data = false;[m
[32m+[m	[32m$report      = give_import_donation_report();[m
[32m+[m	[32m$donor_data  = false;[m
 	$customer_id = false;[m
 [m
 	// check if donor id is not empty[m
[36m@@ -1655,7 +1659,7 @@[m [mfunction give_import_get_user_from_csv( $data, $import_setting = array() ) {[m
 			}[m
 [m
 			if ( ! empty( $customer_id ) || ( isset( $import_setting['create_user'] ) && 0 === absint( $import_setting['create_user'] ) ) ) {[m
[31m-				$donor_data= new Give_Donor( $customer_id, true );[m
[32m+[m				[32m$donor_data = new Give_Donor( $customer_id, true );[m
 [m
 				if ( empty( $donor_data->id ) ) {[m
 [m
[36m@@ -1665,8 +1669,8 @@[m [mfunction give_import_get_user_from_csv( $data, $import_setting = array() ) {[m
 [m
 					$payment_title = ( isset( $data['form_title'] ) ? $data['form_title'] : ( isset( $form ) ? $form->get_name() : esc_html( 'New Form', 'give' ) ) );[m
 					$donor_args    = array([m
[31m-						'name'    => ! is_email( $payment_title ) ? $data['first_name'] . ' ' . $data['last_name'] : '',[m
[31m-						'email'   => $data['email'],[m
[32m+[m						[32m'name'  => ! is_email( $payment_title ) ? $data['first_name'] . ' ' . $data['last_name'] : '',[m
[32m+[m						[32m'email' => $data['email'],[m
 					);[m
 [m
 					if ( ! empty( $customer_id ) ) {[m
[36m@@ -1677,7 +1681,7 @@[m [mfunction give_import_get_user_from_csv( $data, $import_setting = array() ) {[m
 [m
 					// Adding notes that donor is being imported from CSV.[m
 					$current_user = wp_get_current_user();[m
[31m-					$donor_data->add_note( esc_html( wp_sprintf( __( 'This donor was imported by %s' , 'give' ), $current_user->user_email ) ) );[m
[32m+[m					[32m$donor_data->add_note( esc_html( wp_sprintf( __( 'This donor was imported by %s', 'give' ), $current_user->user_email ) ) );[m
 [m
 					$report['create_donor'] = ( ! empty( $report['create_donor'] ) ? ( absint( $report['create_donor'] ) + 1 ) : 1 );[m
 				} else {[m
