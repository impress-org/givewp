/**
* Internal dependencies
*/
const { __ } = wp.i18n;
const {	BlockControls } = wp.blocks;
import './scss/controls.scss';


/**
 * Render Block Controls
*/
const Controls = ( props ) => {
	// Event(s)
	const onChangeForm = () => {
		props.setAttributes( { prevId: props.attributes.id } );
		props.setAttributes( { id: 0 } );
	};

	return (
		<div className="give-block-controls">

			<div className="control-popup">

				{ /* Change Form */ }
				<div className="control-button change-form" onClick={ onChangeForm } >
					<div>
						<span className="dashicons dashicons-image-rotate"></span><span>{ __( 'Change Form' ) }</span>
					</div>
				</div>

				{ /* Edit Form */ }
				<a
					className="control-button edit-form"
					href={ `${ wpApiSettings.schema.url }/wp-admin/post.php?post=${ props.attributes.id }&action=edit` }
					target="_blank"
					tooltip={ __( 'Edit donation form' ) }
				>
					<div>
						<span className="dashicons dashicons-edit"></span><span>{ __( 'Edit Form' ) }</span>
					</div>
				</a>

			</div>
		</div>
	);
};

export default Controls;
