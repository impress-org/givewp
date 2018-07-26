/**
 * Internal dependencies
 */
const {BaseControl} = wp.components,
	{withInstanceId} = wp.compose,
	{Component} = wp.element,
	{isEmpty} = _;

class GiveSelectControl extends Component{
	constructor(props){
		super(props);

		this.onChangeHandler = this.onChangeHandler.bind( this );
	}

	onChangeHandler(event){
		if ( this.props.onChange ) {
			this.props.onChange(event);
		}
	}


	// Disable reason: A select with an onchange throws a warning

	render(){
		const {label, help, instanceId, options = [], name, ...props } = this.props;
		const id = `give-inspector-select-control-${ instanceId }`;

		/* eslint-disable jsx-a11y/no-onchange */
		return ! isEmpty( options ) && (
			<BaseControl label={ label } id={ id } help={ help }>
				<select
					id={ id }
					name={ name }
					className="blocks-select-control__input"
					onChange={ this.onChangeHandler }
					aria-describedby={ !! help ? id + '__help' : undefined }
					{...props}
				>
					{ options.map( ( option ) =>
						<option
							key={ option.value }
							value={ option.value }
						>
							{ option.label }
						</option>
					) }
				</select>
			</BaseControl>
		);
		/* eslint-enable jsx-a11y/no-onchange */
	}
}

export default withInstanceId( GiveSelectControl );
