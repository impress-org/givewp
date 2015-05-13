<?php
/**
 * PDF Report Generation Functions
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate PDF Reports
 *
 * Generates PDF report on donations and income for all forms for the current year.
 *
 * @since  1.0
 *
 * @param string $data
 *
 * @uses   give_pdf
 */
function give_generate_pdf( $data ) {

	if ( ! current_user_can( 'view_give_reports' ) ) {
		wp_die( __( 'You do not have permission to generate PDF sales reports', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
	}

	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'give_generate_pdf' ) ) {
		wp_die( __( 'Nonce verification failed', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
	}

	require_once GIVE_PLUGIN_DIR . '/includes/libraries/fpdf/fpdf.php';
	require_once GIVE_PLUGIN_DIR . '/includes/libraries/fpdf/give_pdf.php';

	$daterange = date_i18n( get_option( 'date_format' ), mktime( 0, 0, 0, 1, 1, date( 'Y' ) ) ) . ' ' . utf8_decode( __( 'to', 'give' ) ) . ' ' . date_i18n( get_option( 'date_format' ) );

	$pdf = new give_pdf();
	$pdf->AddPage( 'L', 'A4' );

	$pdf->SetTitle( utf8_decode( __( 'Donation report for the current year for all forms', 'give' ) ) );
	$pdf->SetAuthor( utf8_decode( __( 'Give - Democratizing Generosity', 'give' ) ) );
	$pdf->SetCreator( utf8_decode( __( 'Give - Democratizing Generosity', 'give' ) ) );

	$pdf->Image( GIVE_PLUGIN_URL . 'assets/images/give-logo-small.png', 247, 8 );

	$pdf->SetMargins( 8, 8, 8 );
	$pdf->SetX( 8 );

	$pdf->SetFont( 'Helvetica', '', 16 );
	$pdf->SetTextColor( 50, 50, 50 );
	$pdf->Cell( 0, 3, utf8_decode( __( 'Donation report for the current year for all forms', 'give' ) ), 0, 2, 'L', false );

	$pdf->SetFont( 'Helvetica', '', 13 );
	$pdf->Ln();
	$pdf->SetTextColor( 150, 150, 150 );
	$pdf->Cell( 0, 6, utf8_decode( __( 'Date Range: ', 'give' ) ) . $daterange, 0, 2, 'L', false );
	$pdf->Ln();
	$pdf->SetTextColor( 50, 50, 50 );
	$pdf->SetFont( 'Helvetica', '', 14 );
	$pdf->Cell( 0, 10, utf8_decode( __( 'Table View', 'give' ) ), 0, 2, 'L', false );
	$pdf->SetFont( 'Helvetica', '', 12 );

	$pdf->SetFillColor( 238, 238, 238 );
	$pdf->Cell( 70, 6, utf8_decode( __( 'Form Name', 'give' ) ), 1, 0, 'L', true );
	$pdf->Cell( 30, 6, utf8_decode( __( 'Price', 'give' ) ), 1, 0, 'L', true );
	$pdf->Cell( 50, 6, utf8_decode( __( 'Categories', 'give' ) ), 1, 0, 'L', true );
	$pdf->Cell( 50, 6, utf8_decode( __( 'Tags', 'give' ) ), 1, 0, 'L', true );
	$pdf->Cell( 45, 6, utf8_decode( __( 'Number of Donations', 'give' ) ), 1, 0, 'L', true );
	$pdf->Cell( 35, 6, utf8_decode( __( 'Income to Date', 'give' ) ), 1, 1, 'L', true );

	$year       = date( 'Y' );
	$give_forms = get_posts( array( 'post_type' => 'give_forms', 'year' => $year, 'posts_per_page' => - 1 ) );

	if ( $give_forms ):
		$pdf->SetWidths( array( 70, 30, 50, 50, 45, 35 ) );

		foreach ( $give_forms as $form ):
			$pdf->SetFillColor( 255, 255, 255 );

			$title = $form->post_title;

			if ( give_has_variable_prices( $form->ID ) ) {

				$prices = give_get_variable_prices( $form->ID );

				$first = $prices[0]['_give_amount'];
				$last  = array_pop( $prices );
				$last  = $last['_give_amount'];

				if ( $first < $last ) {
					$min = $first;
					$max = $last;
				} else {
					$min = $last;
					$max = $first;
				}

				$price = html_entity_decode( give_currency_filter( give_format_amount( $min ) ) . ' - ' . give_currency_filter( give_format_amount( $max ) ) );
			} else {
				$price = html_entity_decode( give_currency_filter( give_get_form_price( $form->ID ) ) );
			}

			$categories = get_the_term_list( $form->ID, 'give_forms_category', '', ', ', '' );
			$categories = ! is_wp_error( $categories ) ? strip_tags( $categories ) : '';

			$tags = get_the_term_list( $form->ID, 'give_forms_tag', '', ', ', '' );
			$tags = ! is_wp_error( $tags ) ? strip_tags( $tags ) : '';

			$sales    = give_get_form_sales_stats( $form->ID );
			$link     = get_permalink( $form->ID );
			$earnings = html_entity_decode( give_currency_filter( give_get_form_earnings_stats( $form->ID ) ) );

			if ( function_exists( 'iconv' ) ) {
				// Ensure characters like euro; are properly converted.
				$price    = iconv( 'UTF-8', 'windows-1252', utf8_encode( $price ) );
				$earnings = iconv( 'UTF-8', 'windows-1252', utf8_encode( $earnings ) );
			}

			$pdf->Row( array( $title, $price, $categories, $tags, $sales, $earnings ) );
		endforeach;
	else:
		$pdf->SetWidths( array( 280 ) );
		$title = utf8_decode( sprintf( __( 'No %s found.', 'give' ), give_get_forms_label_plural() ) );
		$pdf->Row( array( $title ) );
	endif;

	$pdf->Ln();
	$pdf->SetTextColor( 50, 50, 50 );
	$pdf->SetFont( 'Helvetica', '', 14 );
	$pdf->Cell( 0, 10, utf8_decode( __( 'Graph View', 'give' ) ), 0, 2, 'L', false );
	$pdf->SetFont( 'Helvetica', '', 12 );

	$image = html_entity_decode( urldecode( give_draw_chart_image() ) );
	$image = str_replace( ' ', '%20', $image );

	$pdf->SetX( 25 );
	$pdf->Image( $image . '&file=.png' );
	$pdf->Ln( 7 );
	$pdf->Output( apply_filters( 'give_sales_earnings_pdf_export_filename', 'give-report-' . date_i18n( 'Y-m-d' ) ) . '.pdf', 'D' );
}

add_action( 'give_generate_pdf', 'give_generate_pdf' );

/**
 * Draws Chart for PDF Report
 *
 * Draws the sales and earnings chart for the PDF report and then retrieves the
 * URL of that chart to display on the PDF Report
 *
 * @since  1.1.4.0
 * @uses   GoogleChart
 * @uses   GoogleChartData
 * @uses   GoogleChartShapeMarker
 * @uses   GoogleChartTextMarker
 * @uses   GoogleChartAxis
 * @return string $chart->getUrl() URL for the Google Chart
 */
function give_draw_chart_image() {
	require_once GIVE_PLUGIN_DIR . '/includes/libraries/googlechartlib/GoogleChart.php';
	require_once GIVE_PLUGIN_DIR . '/includes/libraries/googlechartlib/markers/GoogleChartShapeMarker.php';
	require_once GIVE_PLUGIN_DIR . '/includes/libraries/googlechartlib/markers/GoogleChartTextMarker.php';

	$chart = new GoogleChart( 'lc', 900, 330 );

	$i        = 1;
	$earnings = "";
	$sales    = "";

	while ( $i <= 12 ) :
		$earnings .= give_get_earnings_by_date( null, $i, date( 'Y' ) ) . ",";
		$sales .= give_get_sales_by_date( null, $i, date( 'Y' ) ) . ",";
		$i ++;
	endwhile;

	$earnings_array = explode( ",", $earnings );
	$sales_array    = explode( ",", $sales );

	$i = 0;
	while ( $i <= 11 ) {
		if ( empty( $sales_array[ $i ] ) ) {
			$sales_array[ $i ] = 0;
		}
		$i ++;
	}

	$min_earnings   = 0;
	$max_earnings   = max( $earnings_array );
	$earnings_scale = round( $max_earnings, - 1 );

	$data = new GoogleChartData( array(
		$earnings_array[0],
		$earnings_array[1],
		$earnings_array[2],
		$earnings_array[3],
		$earnings_array[4],
		$earnings_array[5],
		$earnings_array[6],
		$earnings_array[7],
		$earnings_array[8],
		$earnings_array[9],
		$earnings_array[10],
		$earnings_array[11]
	) );

	$data->setLegend( __( 'Income', 'give' ) );
	$data->setColor( '1b58a3' );
	$chart->addData( $data );

	$shape_marker = new GoogleChartShapeMarker( GoogleChartShapeMarker::CIRCLE );
	$shape_marker->setColor( '000000' );
	$shape_marker->setSize( 7 );
	$shape_marker->setBorder( 2 );
	$shape_marker->setData( $data );
	$chart->addMarker( $shape_marker );

	$value_marker = new GoogleChartTextMarker( GoogleChartTextMarker::VALUE );
	$value_marker->setColor( '000000' );
	$value_marker->setData( $data );
	$chart->addMarker( $value_marker );

	$data = new GoogleChartData( array(
		$sales_array[0],
		$sales_array[1],
		$sales_array[2],
		$sales_array[3],
		$sales_array[4],
		$sales_array[5],
		$sales_array[6],
		$sales_array[7],
		$sales_array[8],
		$sales_array[9],
		$sales_array[10],
		$sales_array[11]
	) );
	$data->setLegend( __( 'Donations', 'give' ) );
	$data->setColor( 'ff6c1c' );
	$chart->addData( $data );

	$chart->setTitle( __( 'Donations by Month for all Give Forms', 'give' ), '336699', 18 );

	$chart->setScale( 0, $max_earnings );

	$y_axis = new GoogleChartAxis( 'y' );
	$y_axis->setDrawTickMarks( true )->setLabels( array( 0, $max_earnings ) );
	$chart->addAxis( $y_axis );

	$x_axis = new GoogleChartAxis( 'x' );
	$x_axis->setTickMarks( 5 );
	$x_axis->setLabels( array(
		__( 'Jan', 'give' ),
		__( 'Feb', 'give' ),
		__( 'Mar', 'give' ),
		__( 'Apr', 'give' ),
		__( 'May', 'give' ),
		__( 'June', 'give' ),
		__( 'July', 'give' ),
		__( 'Aug', 'give' ),
		__( 'Sept', 'give' ),
		__( 'Oct', 'give' ),
		__( 'Nov', 'give' ),
		__( 'Dec', 'give' )
	) );
	$chart->addAxis( $x_axis );

	$shape_marker = new GoogleChartShapeMarker( GoogleChartShapeMarker::CIRCLE );
	$shape_marker->setSize( 6 );
	$shape_marker->setBorder( 2 );
	$shape_marker->setData( $data );
	$chart->addMarker( $shape_marker );

	$value_marker = new GoogleChartTextMarker( GoogleChartTextMarker::VALUE );
	$value_marker->setData( $data );
	$chart->addMarker( $value_marker );

	return $chart->getUrl();
}
