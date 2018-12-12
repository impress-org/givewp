/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { Button } = wp.components;

/**
 * Internal dependencies
 */
import { getSiteUrl } from '../../utils';
import GiveBlankSlate from '../blank-slate';

/**
 * Render No forms Found UI
*/

const NoForms = () => {
	return (
		<GiveBlankSlate title={ __( 'No donation forms found.' ) }
			description={ __( 'The first step towards accepting online donations is to create a form.' ) }
			helpLink>
			<Button
				isPrimary
				isLarge
				className="give-blank-slate__cta"
				href={ `${ getSiteUrl() }/wp-admin/post-new.php?post_type=give_forms` }>
				{ __( 'Create Donation Form' ) }
			</Button>
		</GiveBlankSlate>
	);
};

export default NoForms;
