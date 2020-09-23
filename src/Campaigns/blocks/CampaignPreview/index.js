const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InnerBlocks } = wp.blockEditor;
const { PluginDocumentSettingPanel } = wp.editPost;

import GoalAmountSetting from '../components/goal-amount-setting';

export default registerBlockType( 'give/campaign-preview', {
	title: __( 'Campaign Preview' ),
	description: __( '...' ),
	category: 'give',
	keywords: [
		// ...
	],
	supports: {
		html: false,
	},
	edit: () => {
		return (
			<>
				<PluginDocumentSettingPanel
					open={ true }
					name="campaign-preview"
					title="Campaign Settings"
				>
					<GoalAmountSetting />
				</PluginDocumentSettingPanel>
				<InnerBlocks templateLock="all" />
			</>
		);
	},
	save: () => {
		// Server side rendering via shortcode
		return <InnerBlocks.Content />;
	},
} );
