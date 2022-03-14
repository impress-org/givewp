/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Button } from '@wordpress/components';

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
		<GiveBlankSlate title={ __( 'No donation forms found.', 'give' ) }
			description={ __( 'The first step towards accepting online donations is to create a form.', 'give' ) }
			helpLink>
			<Button
				isPrimary
				isLarge
				className="give-blank-slate__cta"
				href={ `${ getSiteUrl() }/wp-admin/post-new.php?post_type=give_forms` }>
				{ __( 'Create Donation Form', 'give' ) }
			</Button>
		</GiveBlankSlate>
	);
};

export default NoForms;
