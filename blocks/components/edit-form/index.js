/**
 * Block dependencies
 */
import GiveBlankSlate from '../blank-slate/index';

/**
 * Internal dependencies
 */
const { __ } = wp.i18n;
const { Button } = wp.components;
const { Component } = wp.element;

/**
 * Render No forms Found UI
 */

class EditForm extends Component  {
	render(){
		return (
			<GiveBlankSlate title={ __( 'Edit Form.' ) }
							description={ __( 'You can not see form preview because there is something went wrong with donation form settings.' ) }>
				<Button isPrimary
						isLarge
						target='_blank'
						href={ `${ wpApiSettings.schema.url }/wp-admin/post.php?post=${this.props.formId}&action=edit` }>
					{ __( 'Edit Donation Form' ) }
				</Button>
			</GiveBlankSlate>
		);
	}
};

export default EditForm;
