/**
 * Internal dependencies
 */
const {BaseControl, FormToggle} = wp.components,
	{withInstanceId} = wp.compose,
	{Component} = wp.element;

class GiveToggleControl extends Component {
	constructor(props) {
		super( ...props );

		this.onChange = this.onChange.bind( this );
	}

	onChange( event ) {
		if ( this.props.onChange ) {
			this.props.onChange(event);
		}
	}

	render() {
		const { label, checked, help, instanceId, name } = this.props;
		const id = `give-inspector-toggle-control-${ instanceId }`;

		let describedBy;
		if ( help ) {
			describedBy = id + '__help';
		}

		return (
			<BaseControl
				label={ label }
				id={ id }
				help={ help }
				className="blocks-toggle-control"
			>
				<FormToggle
					id={ id }
					name={ name }
					checked={ checked }
					onChange={ this.onChange }
					aria-describedby={ describedBy }
				/>
			</BaseControl>
		);
	}
}

export default withInstanceId( GiveToggleControl );
