// Internal dependencies
import Content from './content';

export const registerEditProfileTab = () => window.giveDonorProfile.utils.registerTab( {
	label: 'Edit Profile',
	icon: 'cog',
	slug: 'edit-profile',
	content: Content,
} );
