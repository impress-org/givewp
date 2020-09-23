const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InspectorControls, InnerBlocks } = wp.blockEditor;
const { PanelBody } = wp.components;

export default registerBlockType( 'give/campaign-editor', {
	title: __( 'Campaign Editor' ),
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
				<InspectorControls key="inspector">
					<PanelBody title={ __( 'Settings' ) }>
						HERE
					</PanelBody>
				</InspectorControls>
				<InnerBlocks templateLock="all" />
			</>
		);
	},
	save: () => {
		// Server side rendering via shortcode
		return <InnerBlocks.Content />;
	},
} );
