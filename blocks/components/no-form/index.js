/**
 * Block dependencies
 */
import GiveBlankSlate from '../blank-slate/index';

/**
 * Internal dependencies
 */
const { __ } = wp.i18n;
const { Button } = wp.components;

/**
 * Render No forms Found UI
*/

const NoForms = () => {
	return (
		<GiveBlankSlate title={ __( 'No donation forms found.' ) }
			description={ __( 'The first step towards accepting online donations is to create a form.' ) }
			helpLink>
			<Button isPrimary
				isLarge
				href={ `${ wpApiSettings.schema.url }/wp-admin/post-new.php?post_type=give_forms` }>
				{ __( 'Create Donation Form' ) }
			</Button>
		</GiveBlankSlate>
	);
};

export default NoForms;
