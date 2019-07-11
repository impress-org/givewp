/**
 * Wordpress dependencies
 */
const { Component } = wp.element;

/**
 * Render ChosenSelect Control
*/
class ChosenSelect extends Component {
	constructor( props ) {
		super( props );

		this.state = {};

		this.saveSetting = this.saveSetting.bind( this );
		this.saveState = this.saveState.bind( this );
	}

	saveSetting( name, value ) {
		this.props.setAttributes( {
			[ name ]: value,
		} );
	}

	saveState( name, value ) {
		this.setState( {
			[ name ]: value,
		} );
	}

	componentDidMount() {
		this.$el = jQuery( this.el );
		this.$input = this.$el.chosen( {
			reset_search_field_on_update: false,
		} ).data( 'chosen' );

		this.handleChange = this.handleChange.bind( this );

		this.$input.search_field.on( 'change', this.handleChange );
	}

	componentWillUnmount() {
		this.$el.off( 'change', this.handleChange );
		this.$el.chosen( 'destroy' );
	}

	handleChange( e ) {
		this.search = e.target.value;
		this.props.onChange( e.target.value );
	}

	componentDidUpdate( prevProps ) {
		if ( prevProps.options !== this.props.options ) {
			this.$input.search_field.autocomplete( {
				source: function( request, response ) {
					const data = {
						action: 'give_block_donation_form_search_results',
						search: request.term,
					};

					jQuery.post( ajaxurl, data, ( responseData ) => {
						jQuery( '.give-block-chosen-select' ).empty();
						responseData = JSON.parse( responseData );
						response( jQuery.map( responseData, function( item ) {
							jQuery( '.give-block-chosen-select' ).append( '<option value="' + item.id + '">' + item.name + '</option>' );
						} ) );
						jQuery( '.give-block-chosen-select' ).trigger( 'chosen:updated' );
					} );
				},
			} );
			this.$input.search_field.val( this.search );
			// this.$el.trigger( 'chosen:updated' );
		}
	}

	render() {
		return (
			<div>
				<select className="chosen-select give-select give-select-chosen give-block-chosen-select" ref={ el => this.el = el }>
					{ this.props.options.map( ( option, index ) =>
						<option
							key={ `${ option.label }-${ option.value }-${ index }` }
							value={ option.value }
						>
							{ option.label }
						</option>
					) }
				</select>
			</div>
		);
	}
}

export default ChosenSelect;
