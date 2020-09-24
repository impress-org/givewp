const { __ } = wp.i18n;
const { useSelect } = wp.data;
const { __experimentalNumberControl } = wp.components;
const { useEntityProp } = wp.coreData;

const NumberControl = __experimentalNumberControl;

const GoalAmountMeta = () => {
	const postType = useSelect(
		( select ) => select( 'core/editor' ).getCurrentPostType(),
		[]
	);
	const [ meta, setMeta ] = useEntityProp(
		'postType',
		postType,
		'meta'
	);
	return {
		goalAmount: meta[ 'goal_amount' ],
		/* eslint-disable-next-line no-undef */
		goalAmountFormatted: Give.fn.formatCurrency( meta[ 'goal_amount' ], { precision: 0 } ),
		updateGoalAmount: ( newValue ) => {
			setMeta( { ...meta, goal_amount: newValue } );
		},
	};
};

const GoalAmountSetting = () => {
	const { goalAmount, updateGoalAmount } = GoalAmountMeta();

	return (
		<NumberControl
			label={ __( 'Goal Amount' ) }
			isShiftStepEnabled={ true }
			onChange={ updateGoalAmount }
			shiftStep={ 10 }
			value={ goalAmount }
		/>
	);
};

export {
	GoalAmountMeta,
	GoalAmountSetting,
};
