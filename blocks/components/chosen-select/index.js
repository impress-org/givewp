/**
 * WordPress dependencies
 */
const { Component } = wp.element;
const { BaseControl } = wp.components;

/**
 * Render ChosenSelect Control
*/
class ChosenSelect extends Component {
	constructor( props ) {
		super( props );

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
		const { value } = this.props;

		this.$el = jQuery( this.el );
		this.$el.val( value );

		this.$input = this.$el.chosen( {
			width: '100%',
		} ).data( 'chosen' );

		this.handleChange = this.handleChange.bind( this );

		this.$el.on( 'change', this.handleChange );
	}

	componentWillUnmount() {
		this.$el.off( 'change', this.handleChange );
		this.$el.chosen( 'destroy' );
	}

	handleChange( e ) {
		this.props.onChange( e.target.value );
	}

	componentDidUpdate() {
		const $searchField = jQuery( '.chosen-base-control' ).closest( '.chosen-container' ).find( '.chosen-search-input' );
		this.$input.search_field.autocomplete( {
			source: function( request, response ) {
				const data = {
					action: 'give_block_donation_form_search_results',
					search: request.term,
				};

				jQuery.post( ajaxurl, data, ( responseData ) => {
					jQuery( '.give-block-chosen-select' ).empty();
					responseData = JSON.parse( responseData );

					if ( responseData.length > 0 ) {
						response( jQuery.map( responseData, function( item ) {
							jQuery( '.give-block-chosen-select' ).append( '<option value="' + item.id + '">' + item.name + '</option>' );
						} ) );
						jQuery( '.give-block-chosen-select' ).trigger( 'chosen:updated' );
						$searchField.val( request.term );
					}
				} );
			},
		} );
	}

	render() {
		return (
			<BaseControl className="give-chosen-base-control">
				<select className="give-select give-select-chosen give-block-chosen-select" ref={ el => this.el = el }>
					{ this.props.options.map( ( option, index ) =>
						<option
							key={ `${ option.label }-${ option.value }-${ index }` }
							value={ option.value }
						>
							{ option.label }
						</option>
					) }
				</select>
			</BaseControl>
		);
	}
}

export default ChosenSelect;
