const { __ } = wp.i18n;
const { useSelect } = wp.data;
const { __experimentalNumberControl } = wp.components;
const { useEntityProp } = wp.coreData;

const NumberControl = __experimentalNumberControl;

export default () => {
	const postType = useSelect(
		( select ) => select( 'core/editor' ).getCurrentPostType(),
		[]
	);
	const [ meta, setMeta ] = useEntityProp(
		'postType',
		postType,
		'meta'
	);
	const metaFieldValue = meta[ 'goal_amount' ];
	function updateMetaValue( newValue ) {
		setMeta( { ...meta, goal_amount: newValue } );
	}

	return (
		<NumberControl
			label={ __( 'Goal Amount' ) }
			isShiftStepEnabled={ true }
			onChange={ updateMetaValue }
			shiftStep={ 10 }
			value={ metaFieldValue }
		/>
	);
};
