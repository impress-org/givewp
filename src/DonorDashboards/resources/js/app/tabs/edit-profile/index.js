// Internal dependencies
import Content from './content';
import { __ } from '@wordpress/i18n';

export const registerEditProfileTab = () => window.giveDonorDashboard.utils.registerTab( {
	label: __( 'Edit Profile', 'give' ),
	icon: 'cog',
	slug: 'edit-profile',
	content: Content,
} );
