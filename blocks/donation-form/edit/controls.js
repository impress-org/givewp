/**
* Internal dependencies
*/
const { __ } = wp.i18n;
const {	BlockControls } = wp.blocks;
const {
	IconButton,
	Toolbar,
} = wp.components;

const Controls = ( props ) => {
	return (
		<BlockControls key="toolbar">
			<Toolbar>
				<IconButton
					icon="image-rotate"
					label={ __( 'Change Form' ) }
					onClick={ () => props.onChangeForm( 'changeForm' ) }
					tooltip="Select different donation form to display">
					&nbsp; Change Form
				</IconButton>
			</Toolbar>
		</BlockControls>
	);
};

export default Controls;
