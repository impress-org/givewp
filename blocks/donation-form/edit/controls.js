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

			<Toolbar>
				<IconButton
					icon="edit"
					label={ __( 'Edit Form' ) }
					href={ `${ wpApiSettings.schema.url }/wp-admin/post.php?post=${ props.attributes.id }&action=edit` }
					target="_blank"
					tooltip="Edit donation form">
					&nbsp; Edit Form
				</IconButton>
			</Toolbar>
		</BlockControls>
	);
};

export default Controls;
