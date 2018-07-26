/**
 * Internal dependencies
 */

const {Component} = wp.element,
	{BaseControl} = wp.components,
	{withInstanceId} = wp.compose;

class GiveTextControl extends Component {
	constructor(props){
		super( ...props );

		this.onChangeHandler = this.onChangeHandler.bind( this );
	}

	onChangeHandler(event){
		if ( this.props.onChange ) {
			this.props.onChange(event);
		}
	}

	onBlurHandler(event){
		if ( this.props.onBlur ) {
			this.props.onBlur(event);
		}
	}

	render(){
		const { label, name, value, help, instanceId, type = 'text', ...props } = this.props,
			  id = `inspector-text-control-${ instanceId }`;

		return (
			<BaseControl label={ label } id={ id } help={ help }>
				<input className="blocks-text-control__input"
					   type={ type }
					   name={ name }
					   id={ id }
					   value={ value }
					   onChange={ this.onChangeHandler }
					   onBlur={ this.onBlurHandler }
					   aria-describedby={ !! help ? id + '__help' : undefined }
					   { ...props }
				/>
			</BaseControl>
		);
	}
}

export default withInstanceId( GiveTextControl );
