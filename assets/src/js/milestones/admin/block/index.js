const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
const ServerSideRender = wp.serverSideRender;

import GiveLogo from './components/logo';

const registerMilestoneBlock = () => registerBlockType( 'give/milestone', {
	title: __( 'Milestone' ),
	description: __( 'The GiveWP Milestone block inserts an progress bar indicating donations raised across multiple forms. Each Milestone\'s presentation can be customized below.' ),
	category: 'give',
	icon: <GiveLogo color="grey" />,
	keywords: [
		__( 'donation' ),
		__( 'milestone' ),
	],

	edit: function( { attributes } ) {
		return (
			<ServerSideRender
				block="give/milestone"
				attributes={ attributes }
			/>
		);
	},
} );

export default registerMilestoneBlock;
