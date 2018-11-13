/**
* WordPress dependencies
*/
const { __ } = wp.i18n;

/**
* Internal dependencies
*/
import './scss/controls.scss';
import { getSiteUrl } from '../../utils';

/**
 * Render Block Controls
 *
 * @param {object} props component props
 * @returns {object} JSX Object
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
					href={ `${ getSiteUrl() }/wp-admin/post.php?post=${ props.attributes.id }&action=edit` }
					target="_blank"
					rel="noopener noreferrer"
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
