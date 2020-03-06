<?php
/**
 * Graphs
 *
 * This class handles building pretty report graphs
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2012, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Graph Class
 *
 * @since 1.0
 */
class Give_Graph {

	/*
	Simple example:

	data format for each point: array( location on x, location on y )

	$data = array(

		'Label' => array(
			array( 1, 5 ),
			array( 3, 8 ),
			array( 10, 2 )
		),

		'Second Label' => array(
			array( 1, 7 ),
			array( 4, 5 ),
			array( 12, 8 )
		)
	);

	$graph = new Give_Graph( $data );
	$graph->display();

	*/

	/**
	 * Data to graph
	 *
	 * @var array
	 * @since 1.0
	 */
	private $data;

	/**
	 * Unique ID for the graph
	 *
	 * @var string
	 * @since 1.0
	 */
	private $id = '';

	/**
	 * Graph options
	 *
	 * @var array
	 * @since 1.0
	 */
	private $options = array();

	/**
	 * Get things started
	 *
	 * @since 1.0
	 *
	 * @param array $_data
	 * @param array $options
	 */
	public function __construct( $_data, $options = array() ) {

		$this->data = $_data;

		// Generate unique ID
		$this->id = md5( rand() );

		// Setup default options;
		$this->options = apply_filters(
			'give_graph_args',
			array(
				'y_mode'          => null,
				'x_mode'          => null,
				'y_decimals'      => 0,
				'x_decimals'      => 0,
				'y_position'      => 'right',
				'time_format'     => '%d/%b',
				'ticksize_unit'   => 'day',
				'ticksize_num'    => 1,
				'multiple_y_axes' => false,
				'bgcolor'         => '#f9f9f9',
				'bordercolor'     => '#eee',
				'color'           => '#bbb',
				'borderwidth'     => 1,
				'bars'            => true,
				'lines'           => false,
				'points'          => true,
				'dataType'        => array(),
			)
		);

		$this->options = wp_parse_args( $options, $this->options );
	}

	/**
	 * Set an option
	 *
	 * @param $key   The option key to set
	 * @param $value The value to assign to the key
	 *
	 * @since 1.0
	 */
	public function set( $key, $value ) {
		$this->options[ $key ] = $value;
	}

	/**
	 * Get an option
	 *
	 * @param $key The option key to get
	 *
	 * @since 1.0
	 */
	public function get( $key ) {
		return isset( $this->options[ $key ] ) ? $this->options[ $key ] : false;
	}

	/**
	 * Get graph data
	 *
	 * @since 1.0
	 */
	public function get_data() {
		return apply_filters( 'give_get_graph_data', $this->data, $this );
	}

	/**
	 * Load the graphing library script
	 *
	 * @since 1.0
	 */
	public function load_scripts() {
		// Use minified libraries if SCRIPT_DEBUG is turned off
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_register_script( 'jquery-flot-orderbars', GIVE_PLUGIN_URL . 'assets/js/plugins/jquery.flot.orderBars' . $suffix . '.js', array( 'jquery-flot' ), GIVE_VERSION );
		wp_enqueue_script( 'jquery-flot-orderbars' );

		wp_register_script( 'jquery-flot-time', GIVE_PLUGIN_URL . 'assets/js/plugins/jquery.flot.time' . $suffix . '.js', array( 'jquery-flot' ), GIVE_VERSION );
		wp_enqueue_script( 'jquery-flot-time' );

		wp_register_script( 'jquery-flot-resize', GIVE_PLUGIN_URL . 'assets/js/plugins/jquery.flot.resize' . $suffix . '.js', array( 'jquery-flot' ), GIVE_VERSION );
		wp_enqueue_script( 'jquery-flot-resize' );

		wp_register_script( 'jquery-flot', GIVE_PLUGIN_URL . 'assets/js/plugins/jquery.flot' . $suffix . '.js', false, GIVE_VERSION );
		wp_enqueue_script( 'jquery-flot' );

	}

	/**
	 * Build the graph and return it as a string
	 *
	 * @var array
	 * @since 1.0
	 * @return string
	 */
	public function build_graph() {

		$yaxis_count = 1;

		ob_start();
		?>
		<script type="text/javascript">

			jQuery( document ).ready( function ( $ ) {
				$.plot(
					$( "#give-graph-<?php echo $this->id; ?>" ),
					[
						<?php
							$order = 0;
						foreach ( $this->get_data() as $label => $data ) :
							?>
						{
							label : "<?php echo esc_attr( $label ); ?>",
							id    : "<?php echo sanitize_key( $label ); ?>",
							dataType  : '<?php echo ( ! empty( $this->options['dataType'][ $order ] ) ? $this->options['dataType'][ $order ] : 'count' ); ?>',
							// data format is: [ point on x, value on y ]
							data  : [
							<?php
							foreach ( $data as $point ) {
								echo '[' . implode( ',', $point ) . '],'; }
							?>
							],
							points: {
								show: <?php echo $this->options['points'] ? 'true' : 'false'; ?>,
							},
							bars  : {
								show    : <?php echo $this->options['bars'] ? 'true' : 'false'; ?>,
								barWidth: 100,
				                order: <?php echo $order++; ?>,
								align   : 'center'
							},
							lines : {
								show     : <?php echo $this->options['lines'] ? 'true' : 'false'; ?>,
								fill     : true,
								fillColor: {colors: [{opacity: 0.4}, {opacity: 0.1}]}
							},
							<?php if ( $this->options['multiple_y_axes'] ) : ?>
							yaxis : <?php echo $yaxis_count; ?>
							<?php endif; ?>

						},

							<?php
							$yaxis_count++;
endforeach;
						?>

					],
					{
						// Options
						grid: {
							show           : true,
							aboveData      : false,
							color          : "<?php echo $this->options['color']; ?>",
							backgroundColor: "<?php echo $this->options['bgcolor']; ?>",
							borderColor    : "<?php echo $this->options['bordercolor']; ?>",
							borderWidth    : <?php echo absint( $this->options['borderwidth'] ); ?>,
							clickable      : false,
							hoverable      : true
						},

						colors: ["#66bb6a", "#546e7a"], //Give Colors

						xaxis: {
							mode        : "<?php echo $this->options['x_mode']; ?>",
							timeFormat  : "<?php echo $this->options['x_mode'] == 'time' ? $this->options['time_format'] : ''; ?>",
							tickSize    : "<?php echo $this->options['x_mode'] == 'time' ? '' : 1; ?>",
							<?php if ( $this->options['x_mode'] != 'time' ) : ?>
							tickDecimals: <?php echo $this->options['x_decimals']; ?>
							<?php endif; ?>
						},
						yaxis: {
							position    : 'right',
							min         : 0,
							mode        : "<?php echo $this->options['y_mode']; ?>",
							timeFormat  : "<?php echo $this->options['y_mode'] == 'time' ? $this->options['time_format'] : ''; ?>",
							<?php if ( $this->options['y_mode'] != 'time' ) : ?>
							tickDecimals: <?php echo $this->options['y_decimals']; ?>,
							<?php endif; ?>
							tickFormatter: function(val) {
								return val.toString().replace(/\B(?=(?:\d{3})+(?!\d))/g, give_vars.thousands_separator);
							},
						}
					}
				);

				function give_flot_tooltip( x, y, contents ) {
					$( '<div id="give-flot-tooltip">' + contents + '</div>' ).css( {
						position          : 'absolute',
						display           : 'none',
						top               : y + 5,
						left              : x + 5,
						border            : '1px solid #fdd',
						padding           : '2px',
						'background-color': '#fee',
						opacity           : 0.80
					} ).appendTo( "body" ).fadeIn( 200 );
				}

				var previousPoint = null;
				$( "#give-graph-<?php echo $this->id; ?>" ).bind( "plothover", function ( event, pos, item ) {

					$( "#x" ).text( pos.x.toFixed( 2 ) );
					$( "#y" ).text( pos.y.toFixed( 2 ) );
					if ( item ) {
						if ( previousPoint !== item.dataIndex ) {
							previousPoint = item.dataIndex;
							$( "#give-flot-tooltip" ).remove();
							var x = item.datapoint[0].toFixed( 2 ),
								y = accounting.formatMoney( item.datapoint[1].toFixed( give_vars.currency_decimals ), '', give_vars.currency_decimals, give_vars.thousands_separator, give_vars.decimal_separator );

							if ( item.series.dataType.length &&  item.series.dataType === 'amount' ) {

								if ( give_vars.currency_pos === 'before' ) {

									give_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + give_vars.currency_sign + y );
								} else {
									give_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + y + give_vars.currency_sign );
								}
							} else {
								give_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + parseInt( y ) );
							}
						}
					} else {
						$( "#give-flot-tooltip" ).remove();
						previousPoint = null;
					}
				} );

			} );

		</script>
		<div id="give-graph-<?php echo $this->id; ?>" class="give-graph" style="height: 300px;"></div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Output the final graph
	 *
	 * @since 1.0
	 */
	public function display() {
		/**
		 * Fires before displaying the final graph.
		 *
		 * @since 1.0
		 *
		 * @param Give_Graph $this Graph object.
		 */
		do_action( 'give_before_graph', $this );

		// Build the graph.
		echo $this->build_graph();

		/**
		 * Fires after displaying the final graph.
		 *
		 * @since 1.0
		 *
		 * @param Give_Graph $this Graph object.
		 */
		do_action( 'give_after_graph', $this );
	}

}
