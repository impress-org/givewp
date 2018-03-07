/**
* Internal dependencies
*/
const { __ } = wp.i18n;
const {	BlockControls } = wp.blocks;
const {
	IconButton,
	Toolbar,
} = wp.components;

/**
 * Render Block Controls
*/

const Controls = ( props ) => {
	// Event(s)
	const onChangeForm = () => {
		props.setAttributes( { id: 0 } );
	};

	return (
		<BlockControls key="toolbar">
			<Toolbar>
				<IconButton
					icon="image-rotate"
					label={ __( 'Change Form' ) }
					onClick={ onChangeForm }
					tooltip={ __( 'Select different donation form to display' ) }>
					&nbsp; { __( 'Change Form' ) }
				</IconButton>
			</Toolbar>

			<Toolbar>
				<IconButton
					icon="edit"
					label={ __( 'Edit Form' ) }
					href={ `${ wpApiSettings.schema.url }/wp-admin/post.php?post=${ props.attributes.id }&action=edit` }
					target="_blank"
					tooltip={ __( 'Edit donation form' ) }>
					&nbsp; { __( 'Edit Form' ) }
				</IconButton>
			</Toolbar>
		</BlockControls>
	);
};

export default Controls;
